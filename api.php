<?php
declare(strict_types=1);
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');
$cfg = require __DIR__.'/config.php';

function out($d, int $c=200){ http_response_code($c); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
function db(): PDO {
  global $cfg; static $p;
  if(!$p){
    try{
      $p = new PDO("mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4",$cfg['db_user'],$cfg['db_pass'],
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    }catch(Throwable $e){ out(['error'=>'Database connection failed'],500); }
  }
  return $p;
}
function clean(string $s): string { return trim(preg_replace('/[\r\n]+/',' ',$s)); }
function slugify(string $s): string { return trim(preg_replace('/[^a-z0-9]+/','-',strtolower($s)),'-'); }
function need_post(){ if($_SERVER['REQUEST_METHOD']!=='POST') out(['error'=>'POST required'],405); }
function need_admin(){
  need_post();
  if(empty($_SESSION['admin'])) out(['error'=>'Unauthorized'],401);
  if(!hash_equals($_SESSION['csrf'], $_SERVER['HTTP_X_CSRF'] ?? '')) out(['error'=>'Invalid token'],403);
}

if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$in  = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
$act = $_GET['action'] ?? '';
$isAdmin = !empty($_SESSION['admin']);
$CATS = ['Strategy','Marketing','Company News','Finance','Productivity'];

switch($act){
  case 'me':
    out(['admin'=>$isAdmin,'csrf'=>$_SESSION['csrf']]);

  case 'login':
    need_post();
    $u = (string)($in['user'] ?? ''); $p = (string)($in['pass'] ?? '');
    $stored = $cfg['admin_pass'];
    $okPass = str_starts_with($stored,'$2y$') ? password_verify($p,$stored) : hash_equals($stored,$p);
    if(hash_equals($cfg['admin_user'],$u) && $okPass){
      session_regenerate_id(true); $_SESSION['admin']=true;
      out(['ok'=>true,'csrf'=>$_SESSION['csrf']]);
    }
    sleep(1); out(['ok'=>false,'error'=>'Wrong username or password'],401);

  case 'logout':
    need_post(); $_SESSION=[]; session_destroy(); out(['ok'=>true]);

  case 'posts':
    $all = $isAdmin && isset($_GET['all']);
    $rows = db()->query("SELECT * FROM posts ".($all?'':"WHERE status='published' ")."ORDER BY post_date DESC, id DESC")->fetchAll();
    out(['posts'=>array_map(fn($r)=>[
      'id'=>(int)$r['id'],'slug'=>$r['slug'],'title'=>$r['title'],'cat'=>$r['cat'],'author'=>$r['author'],
      'date'=>$r['post_date'],'read'=>(int)$r['read_min'],'featured'=>(bool)$r['featured'],'status'=>$r['status'],
      'excerpt'=>$r['excerpt'],'body'=>preg_split('/\n\n+/',(string)$r['body']),
      'seoTitle'=>$r['seo_title'],'metaDesc'=>$r['meta_desc']], $rows)]);

  case 'save':
    need_admin();
    $title = clean((string)($in['title'] ?? ''));
    if($title==='') out(['ok'=>false,'error'=>'Title is required'],422);
    $cat = in_array($in['cat'] ?? '', $CATS, true) ? $in['cat'] : 'Strategy';
    $author = in_array($in['author'] ?? '', ['a1','a2','a3'], true) ? $in['author'] : 'a1';
    $status = ($in['status'] ?? '')==='published' ? 'published' : 'draft';
    $date = preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)($in['date'] ?? '')) ? $in['date'] : date('Y-m-d');
    $slug = slugify((string)($in['slug'] ?? '')) ?: slugify($title);
    $body = implode("\n\n", array_map('strval', (array)($in['body'] ?? [])));
    $vals = [$slug,$title,$cat,$author,$date,max(1,min(60,(int)($in['read'] ?? 5))),!empty($in['featured'])?1:0,$status,
             (string)($in['excerpt'] ?? ''),$body,clean((string)($in['seoTitle'] ?? '')),clean((string)($in['metaDesc'] ?? ''))];
    $id = (int)($in['id'] ?? 0);
    try{
      if($id>0){
        $vals[]=$id;
        db()->prepare("UPDATE posts SET slug=?,title=?,cat=?,author=?,post_date=?,read_min=?,featured=?,status=?,excerpt=?,body=?,seo_title=?,meta_desc=? WHERE id=?")->execute($vals);
      } else {
        db()->prepare("INSERT INTO posts (slug,title,cat,author,post_date,read_min,featured,status,excerpt,body,seo_title,meta_desc) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")->execute($vals);
        $id = (int)db()->lastInsertId();
      }
    }catch(PDOException $e){
      out(['ok'=>false,'error'=>$e->getCode()==='23000' ? 'That URL slug is already used' : 'Could not save post'],422);
    }
    out(['ok'=>true,'id'=>$id]);

  case 'delete':
    need_admin();
    db()->prepare("DELETE FROM posts WHERE id=?")->execute([(int)($in['id'] ?? 0)]);
    out(['ok'=>true]);

  case 'subscribe':
    need_post();
    $email = clean((string)($in['email'] ?? ''));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) out(['ok'=>false,'error'=>'Please enter a valid email address.'],422);
    db()->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)")->execute([$email]);
    out(['ok'=>true]);

  case 'contact':
    need_post();
    if(!empty($in['website'])) out(['ok'=>true]);                       // honeypot: bots fill this
    if(time() - ($_SESSION['last_contact'] ?? 0) < 30) out(['ok'=>false,'error'=>'Please wait a moment before sending another message.'],429);
    $name = clean((string)($in['name'] ?? '')); $email = clean((string)($in['email'] ?? '')); $msg = trim((string)($in['message'] ?? ''));
    if($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($msg)<5) out(['ok'=>false,'error'=>'Please fill in your name, a valid email and a message.'],422);
    $name = mb_substr($name,0,120); $msg = mb_substr($msg,0,5000);
    db()->prepare("INSERT INTO contacts (name,email,message,ip) VALUES (?,?,?,?)")->execute([$name,$email,$msg,$_SERVER['REMOTE_ADDR'] ?? '']);
    $subject = '=?UTF-8?B?'.base64_encode("New contact message from $name").'?=';
    $headers = implode("\r\n",["From: {$cfg['mail_from']}","Reply-To: $email","Content-Type: text/plain; charset=UTF-8"]);
    $mailed = @mail($cfg['mail_to'],$subject,"Name: $name\nEmail: $email\n\n$msg",$headers);
    $_SESSION['last_contact'] = time();
    out(['ok'=>true,'mailed'=>$mailed]);   // message is saved in MySQL even if mail() is not configured

  default:
    out(['error'=>'Unknown action'],404);
}
