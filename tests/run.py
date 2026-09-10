"""Python 3 stdlib + PHP CLI + local MySQL/MariaDB. Creates and drops ONLY a random wh_test_* database."""
import base64, concurrent.futures, http.cookiejar, json, os, re, secrets, socket, subprocess, sys, tempfile, time, urllib.request, urllib.error, urllib.parse, pathlib
ROOT=pathlib.Path(__file__).resolve().parents[1]
PHP=os.environ.get('WH_PHP','php')
ENV=os.environ.copy()
ENV.update(APP_ENV='test',APP_REQUIRE_HTTPS='false',APP_BASE_PATH='',DB_NAME='wh_test_'+secrets.token_hex(6),DB_HOST=os.environ.get('DB_HOST','127.0.0.1'),DB_PORT=os.environ.get('DB_PORT','3306'))
for key in ('DB_USER','DB_PASSWORD'):
    if not ENV.get(key):raise SystemExit('Set DB_USER and DB_PASSWORD to a LOCAL test database administrator.')
RESULTS=[]
def php(*args,stdin=None,check=True):
    return subprocess.run([PHP,*args],input=stdin,text=True,capture_output=True,cwd=ROOT,env=ENV,check=check)
def query(sql,params=None):
    return json.loads(php('tests/control.php','query',stdin=json.dumps({'sql':sql,'params':params or []})).stdout)
def scalar(sql,params=None):return next(iter(query(sql,params)[0].values()))
def check(name,condition):
    RESULTS.append({'name':name,'passed':bool(condition)})
    print(('PASS ' if condition else 'FAIL ')+name,flush=True)
    if not condition:raise AssertionError(name)
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args):return None
class Client:
    def __init__(self):
        self.jar=http.cookiejar.CookieJar();self.opener=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(self.jar),NoRedirect());self.token=''
    def request(self,path,body=None,json_body=False,csrf=True,accept_json=False):
        headers={}
        if accept_json:headers['Accept']='application/json'
        if body is not None:
            if csrf:headers['X-CSRF-Token']=self.token
            if json_body:headers['Content-Type']='application/json';body=json.dumps(body).encode()
            else:headers['Content-Type']='application/x-www-form-urlencoded';body=urllib.parse.urlencode(body,doseq=True).encode()
        req=urllib.request.Request(BASE+'/'+path,data=body,headers=headers)
        try:r=self.opener.open(req,timeout=20)
        except urllib.error.HTTPError as e:r=e
        data=r.read();text=data.decode(errors='replace')
        token=re.search(r'<meta name="csrf-token" content="([a-f0-9]+)"',text)
        if token:self.token=token[1]
        return r.status,r.headers,data
    def login(self,name='staff_fixture',password='Fixture-password-2026'):
        self.request('login');old=[c.value for c in self.jar if c.name=='WHSESSID']
        status,headers,_=self.request('login',{'username':name,'password':password})
        new=[c.value for c in self.jar if c.name=='WHSESSID']
        if status==303:self.request(headers['Location'].lstrip('/'))
        return status,old!=new,headers
    def upload(self,filename,content,mime,csrf=True):
        boundary='warehouse-test-'+secrets.token_hex(8)
        body=(f'--{boundary}\r\nContent-Disposition: form-data; name="update_profile"\r\n\r\n1\r\n'
             +f'--{boundary}\r\nContent-Disposition: form-data; name="nama_lengkap"\r\n\r\nProfile Fixture\r\n'
             +f'--{boundary}\r\nContent-Disposition: form-data; name="foto"; filename="{filename}"\r\nContent-Type: {mime}\r\n\r\n').encode()+content+f'\r\n--{boundary}--\r\n'.encode()
        headers={'Content-Type':'multipart/form-data; boundary='+boundary}
        if csrf:headers['X-CSRF-Token']=self.token
        request=urllib.request.Request(BASE+'/profile',data=body,headers=headers)
        try:r=self.opener.open(request,timeout=20)
        except urllib.error.HTTPError as e:r=e
        return r.status,r.read()
    def post(self,path,body,**kwargs):return self.request(path,body,json_body=True,accept_json=True,**kwargs)
def add_request(name,top=1,bottom=0,gender='male'):
    query("INSERT INTO request_form (request_id,perusahaan,brand,nama_sa,gender,qty_top,qty_bottoms,status) VALUES (?,'Uji','Brand Uji','SA Uji',?,?,?,'Pending')",[name,gender,top,bottom])
def tx(name,codes):return {'id_request':name,'id_sales':'1234','barcode_item':codes}
def ret(id,codes):return {'no_return':id,'barcode_return':codes,'kondisi_return':['Kebesaran']*len(codes)}
def parallel(action,data):
    start=time.time()+.4
    def one(d):return json.loads(php('tests/control.php','worker',stdin=json.dumps({'action':action,'data':d,'start_at':start})).stdout)
    with concurrent.futures.ThreadPoolExecutor(len(data)) as pool:return list(pool.map(one,data))
created=False;server=None
session_directory=tempfile.TemporaryDirectory(prefix="warehouse-test-sessions-")
log_file=tempfile.NamedTemporaryFile(mode='w+',prefix='warehouse-test-server-',delete=False)
try:
    php('tests/control.php','setup');created=True
    before=scalar('SELECT COUNT(*) FROM users')
    dry=php('database/migrate.php').stdout
    check('migration dry run leaves legacy schema untouched',scalar("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='auth_version'")==0)
    php('database/migrate.php','--apply','--backup-confirmed')
    php('database/migrate.php','--apply','--backup-confirmed')
    check('migration repeat preserves users and legacy zero ID',scalar('SELECT COUNT(*) FROM users')==before and scalar("SELECT user_id FROM users WHERE username='admin_fixture'")==0)
    check('migration assigns unique log IDs and preserves original IDs',scalar('SELECT COUNT(DISTINCT id) FROM log_activity')==2 and int(scalar('SELECT SUM(legacy_id=0) FROM log_activity'))==2)
    check('invalid legacy staff role restored without admin privileges',scalar("SELECT role FROM users WHERE username='staff_fixture'")=='staff')
    with socket.socket() as sock:sock.bind(('127.0.0.1',0));port=sock.getsockname()[1]
    BASE='http://127.0.0.1:'+str(port)
    server=subprocess.Popen([PHP,'-d','session.save_path='+session_directory.name,'-S','127.0.0.1:'+str(port),'-t',str(ROOT),str(ROOT/'tests/router.php')],cwd=ROOT,env=ENV,stdout=log_file,stderr=log_file)
    for _ in range(100):
        try:
            with socket.create_connection(('127.0.0.1',port),timeout=.2):break
        except OSError:time.sleep(.05)
    anon=Client()
    for endpoint in ['controllers/proses_transaksi','controllers/proses_return','controllers/proses_tambah_item','controllers/proses_generate','controllers/audit_action','controllers/user_controller']:
        check('unauthenticated writes blocked: '+endpoint,anon.post(endpoint,{})[0]==401)
    check('unauthenticated barcode read blocked',anon.request('controllers/cek_barcode?barcode=101010001',accept_json=True)[0]==401)
    anon.request('login')
    check('login requires CSRF',anon.post('login',{'username':'admin_fixture','password':'Fixture-password-2026'},csrf=False)[0]==403)
    admin=Client();status,rotated,_=admin.login('admin_fixture')
    check('legacy zero-ID admin logs in and session ID rotates',status==303 and rotated)
    staff=Client();check('staff login works',staff.login()[0]==303)
    for page in ['index','pending','transaksi','return','stok_barang','warehouse_management','audit_item','generate_barcode','setting','profile','laporan','log_activity']:
        check('page renders: '+page,staff.request(page)[0]==200)
    check('staff cannot manage users',staff.post('controllers/user_controller',{'action':'tambah_user'})[0]==403)
    check('staff cannot open audit period',staff.post('warehouse_management',{'action_type':'toggle_audit','audit_status':'1'})[0]==403)
    check('stock update requires CSRF',staff.post('controllers/proses_tambah_item',{'barcode':'101010001'},csrf=False)[0]==403)
    check('malformed stock barcode rejected',staff.post('controllers/proses_tambah_item',{'barcode':'999999999'})[0]==422)
    codes=['10101'+str(n).zfill(4) for n in range(1,21)]+['201010001','102300001']
    check('valid stock batch accepted',staff.post('controllers/proses_generate',{'action':'simpan_stok_batch','barcodes':codes})[0]==200)
    check('batch replay does not duplicate stock',staff.post('controllers/proses_generate',{'action':'simpan_stok_batch','barcodes':codes})[0]==200 and scalar('SELECT COUNT(*) FROM master_item')==len(codes))
    add_request('REQ-A',top=1)
    for name,payload in [('empty items',tx('REQ-A',[])),('duplicate scan',tx('REQ-A',[codes[0],codes[0]])),('wrong quantity',tx('REQ-A',codes[:2])),('wrong gender',tx('REQ-A',['201010001'])),('zero-category violation',tx('REQ-A',['102300001'])),('missing request',tx('',[codes[0]]))]:
        check('transaction rejects '+name,staff.post('controllers/proses_transaksi',payload)[0]==422)
    check('failed validation changes no stock/header/log',scalar('SELECT COUNT(*) FROM transaksi')==0 and scalar("SELECT COUNT(*) FROM master_item WHERE status_transaksi='Sold Out'")==0)
    status,_,body=staff.post('controllers/proses_transaksi',tx('REQ-A',[codes[0]]));id=json.loads(body).get('transaction_id')
    check('valid transaction commits stock and request',status==200 and scalar("SELECT status FROM request_form WHERE request_id='REQ-A'")=='Done' and scalar('SELECT status_transaksi FROM master_item WHERE barcode=?',[codes[0]])=='Sold Out')
    check('completed request replay rejected',staff.post('controllers/proses_transaksi',tx('REQ-A',[codes[1]]))[0]==422)
    add_request('REQ-B');status,_,body=staff.post('controllers/proses_transaksi',tx('REQ-B',[codes[1]]));id2=json.loads(body)['transaction_id']
    check('retur rejects barcode from another transaction',staff.post('controllers/proses_return',ret(id,[codes[1]]))[0]==422)
    check('mixed valid/invalid return rolls back all items',staff.post('controllers/proses_return',ret(id,[codes[0],codes[1]]))[0]==422 and scalar('SELECT COUNT(*) FROM return_items')==0 and scalar('SELECT status_transaksi FROM master_item WHERE barcode=?',[codes[0]])=='Sold Out')
    bad=ret(id,[codes[0]]);bad['kondisi_return']=['Tidak dikenal']
    check('retur reason validated',staff.post('controllers/proses_return',bad)[0]==422)
    check('valid return becomes Available/Inactive',staff.post('controllers/proses_return',ret(id,[codes[0]]))[0]==200 and scalar('SELECT status_barang FROM master_item WHERE barcode=?',[codes[0]])=='Inactive')
    check('duplicate return rejected',staff.post('controllers/proses_return',ret(id,[codes[0]]))[0]==422)
    check('closed period blocks stock evaluation',staff.post('warehouse_management',{'action_type':'update_status','barcode':codes[0],'status_transaksi':'Available','status_barang':'Active'})[0]==422)
    check('admin can open period',admin.post('warehouse_management',{'action_type':'toggle_audit','audit_status':'1'})[0]==200)
    check('audit cannot invent Sold Out state',staff.post('warehouse_management',{'action_type':'update_status','barcode':codes[0],'status_transaksi':'Sold Out','status_barang':'Active'})[0]==422)
    check('staff can evaluate returned stock in open period',staff.post('warehouse_management',{'action_type':'update_status','barcode':codes[0],'status_transaksi':'Available','status_barang':'Active'})[0]==200)
    check('active stock cannot be reevaluated',staff.post('warehouse_management',{'action_type':'update_status','barcode':codes[0],'status_transaksi':'Available','status_barang':'Inactive'})[0]==422)
    add_request('REQ-REISSUE');status,_,body=staff.post('controllers/proses_transaksi',tx('REQ-REISSUE',[codes[0]]));newid=json.loads(body)['transaction_id']
    check('reissued item cannot return against old transaction',staff.post('controllers/proses_return',ret(id,[codes[0]]))[0]==422)
    check('reissued item can return against current transaction',staff.post('controllers/proses_return',ret(newid,[codes[0]]))[0]==200)
    add_request('REQ-C1');add_request('REQ-C2')
    results=parallel('transaction',[tx('REQ-C1',[codes[2]]),tx('REQ-C2',[codes[2]])])
    check('concurrent issue of same barcode succeeds exactly once',sum(x['ok'] for x in results)==1 and scalar('SELECT COUNT(*) FROM transaksi_detail WHERE barcode=?',[codes[2]])==1)
    add_request('REQ-D1');add_request('REQ-D2')
    results=parallel('transaction',[tx('REQ-D1',[codes[3]]),tx('REQ-D2',[codes[4]])])
    check('concurrent independent transactions get unique numbers',all(x['ok'] for x in results) and results[0]['result']!=results[1]['result'])
    results=parallel('return',[ret(id2,[codes[1]]),ret(id2,[codes[1]])])
    check('concurrent return succeeds exactly once',sum(x['ok'] for x in results)==1 and scalar('SELECT COUNT(*) FROM return_items WHERE transaction_id=?',[id2])==1)
    check('return log records actual authenticated user',scalar("SELECT COUNT(*) FROM log_activity WHERE aktivitas='Proses Return Barang' AND user_id=2")>=1)
    # Fail the final audit insert deliberately: earlier writes must be rolled back.
    add_request('REQ-ROLLBACK')
    query("CREATE TRIGGER test_fail_log BEFORE INSERT ON log_activity FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Test log failure'")
    response=staff.post('controllers/proses_transaksi',tx('REQ-ROLLBACK',[codes[6]]))
    check('audit-log failure rolls back header/request/stock',response[0]==500 and scalar("SELECT COUNT(*) FROM transaksi WHERE request_id='REQ-ROLLBACK'")==0 and scalar('SELECT status_transaksi FROM master_item WHERE barcode=?',[codes[6]])=='Available')
    check('SQL details stay out of error response',b'Test log failure' not in response[2] and b'SQLSTATE' not in response[2])
    query('DROP TRIGGER test_fail_log')
    staff.request('profile')
    check('language change requires CSRF',staff.request('controllers/change_language',{'lang':'id'},csrf=False)[0]==403)
    check('language change works by POST',staff.request('controllers/change_language',{'lang':'id'})[0]==303)
    status,_,_=admin.request('controllers/user_controller',{'action':'tambah_user','username':'new_fixture','nama_lengkap':'New Fixture','password':'Another-fixture-2026','role':'staff'})
    check('admin creates staff with valid role and generated ID',status==303 and scalar("SELECT user_id FROM users WHERE username='new_fixture'")>3)
    first=Client();status,rotated,headers=first.login('first_fixture')
    check('first login requires password activation',status==303 and headers['Location'].endswith('change_password'))
    check('first login cannot access inventory yet',first.post('controllers/proses_tambah_item',{'barcode':'101019999'})[0]==401)
    first.login('first_fixture')
    check('first login updates password and correct user_id',first.request('change_password',{'new_password':'Changed-fixture-2026','confirm_password':'Changed-fixture-2026'})[0]==303)
    check('activated user can access app',first.request('index')[0]==200)
    second=Client();second.login()
    staff.request('profile')
    check('change password succeeds',staff.request('profile',{'update_password':'1','pass_lama':'Fixture-password-2026','pass_baru':'Staff-new-password-2026','konfirmasi_pass':'Staff-new-password-2026'})[0]==200)
    check('password change revokes other sessions',second.request('controllers/cek_barcode?barcode='+codes[0],accept_json=True)[0]==401)
    stored=scalar("SELECT password FROM users WHERE username='staff_fixture'")
    attacker=Client();check('stored hash cannot be used as password',attacker.login('staff_fixture',stored)[0]==401)
    attacker=Client();attacker.request('login')
    for i in range(5):attacker.request('login',{'username':'nonexistent','password':'Wrong-password-2026'})
    check('login throttling survives new browser session',Client().login('nonexistent','Wrong-password-2026')[0]==429)
    check('GET logout cannot mutate session',admin.request('logout')[0]==405)
    admin.request('index')
    check('POST logout succeeds',admin.request('logout',{})[0]==303)
    check('logged out session cannot write',admin.post('controllers/proses_tambah_item',{'barcode':'101019999'})[0]==401)
    staff.request('profile')
    previous_photo=scalar("SELECT foto_profil FROM users WHERE username='staff_fixture'")
    check('upload rejects disguised script content',staff.upload('fake.png',b'<?php echo "untrusted"; ?>','image/png')[0]==200 and scalar("SELECT foto_profil FROM users WHERE username='staff_fixture'")==previous_photo)
    png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAAFklEQVR4nGNkSDnBwMDAxMDAwMDAAAANcgEwSMh0DwAAAABJRU5ErkJggg==')
    check('upload requires CSRF',staff.upload('image.png',png,'image/png',csrf=False)[0]==403)
    status,body=staff.upload('image.png',png,'image/png')
    photo=scalar("SELECT foto_profil FROM users WHERE username='staff_fixture'")
    check('valid image is stored under random PNG name',status==200 and photo!=previous_photo and re.fullmatch(r'user_2_[a-f0-9]{32}\.png',photo) is not None)
    if photo!=previous_photo:
        uploaded=ROOT/'assets/img/profile'/photo
        check('stored upload is a real PNG',uploaded.read_bytes().startswith(b'\x89PNG'))
        uploaded.unlink()
        query("UPDATE users SET foto_profil=? WHERE username='staff_fixture'",[previous_photo])
    # Seed adversarial request text so HTML and Excel outputs are verified with real rows.
    add_request('FR-EXPORT')
    query("UPDATE request_form SET nama_sa=?,brand=?,perusahaan=?,total_harga=12000,pembayaran='cash' WHERE request_id='FR-EXPORT'",['=2+3','<img src=x onerror="window.__warehouseInjection=1">','=2+3'])
    check('export fixture transaction succeeds',staff.post('controllers/proses_transaksi',tx('FR-EXPORT',[codes[7]]))[0]==200)
    status,_,html=staff.request('return')
    check('request text is escaped in return HTML',b'<img src=x onerror="window.__warehouseInjection=1">' not in html and b'&lt;img' in html)
    # Exports must remain usable after dependency/config changes.
    import io, zipfile
    for export in ['controllers/export_excel?type=internal','controllers/export_excel?type=finance','controllers/export_excel?gender=Pria&tipe=Baju&ukuran=S&range_awal=1&range_akhir=3']:
        status,headers,body=staff.request(export)
        check('XLSX export responds: '+export,status==200 and body.startswith(b'PK'))
        with zipfile.ZipFile(io.BytesIO(body)) as workbook:
            sheets=[workbook.read(name) for name in workbook.namelist() if name.startswith('xl/worksheets/sheet') and name.endswith('.xml')]
            check('request text remains text in XLSX: '+export,all(b'<f>2+3</f>' not in sheet for sheet in sheets))
    if os.environ.get('WH_DOM_TEST')=='1':
        add_request('REQ-UI',top=2)
        dom_env=ENV.copy();dom_env['WH_TEST_URL']=BASE
        result=subprocess.run(['node',str(ROOT/'tests/scanner-dom.spec.cjs')],env=dom_env,cwd=ROOT)
        check('DOM scanner with real HTTP validation and form submit',result.returncode==0)
    if os.environ.get('WH_UI_TEST')=='1':
        if os.environ.get('WH_DOM_TEST')=='1':
            raise RuntimeError('Run WH_UI_TEST separately from WH_DOM_TEST; each requires fresh fixture stock.')
        add_request('REQ-UI',top=2)
        ui_env=ENV.copy();ui_env['WH_TEST_URL']=BASE
        result=subprocess.run(['node',str(ROOT/'tests/scanner.spec.cjs')],env=ui_env,cwd=ROOT)
        check('browser scanner and form regression checks',result.returncode==0)
finally:
    if server:
        server.terminate()
        try:server.wait(timeout=10)
        except subprocess.TimeoutExpired:server.kill()
    log_file.close()
    session_directory.cleanup()
    if created:php('tests/control.php','drop',check=False)
    report={'passed':sum(x['passed'] for x in RESULTS),'checks':len(RESULTS),'results':RESULTS,'php':php('-v').stdout.splitlines()[0],'real_browser_run':os.environ.get('WH_UI_TEST')=='1','dom_http_run':os.environ.get('WH_DOM_TEST')=='1'}
    target=ROOT/'tests/results.json';target.write_text(json.dumps(report,indent=2))
    print('Results:',report['passed'],'/',report['checks'],'; server log:',log_file.name,flush=True)
