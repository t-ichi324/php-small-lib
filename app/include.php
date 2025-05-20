<?php 
function h($str){ if($str === null || $str === ''){ return ''; }  return htmlspecialchars($str, ENT_QUOTES, Env::get("charset", 'UTF-8')); }


class SysConf{
    const SITE_NAME = "SAMPLE";
    const MENU_JSON = "menu.json";
    const DEFAULT_ROUTE = "index";
    const DIR_APP = "app";
    const DIR_PAGE = "app/page";
    const DIR_LAYOYT = "app/layout";
    const DIR_PUBLIC = "public";

    const TEMPLATE_HEADER = "share/header.php";
    const TEMPLATE_FOOTER = "share/footer.php";
}

/** 環境判断 */
class Env{
    private static $FILE_ENV = __DIR__ . DIRECTORY_SEPARATOR . "env.ini";
    public static $ini = null;
    /** env.iniのキーに記載されている文字列を返す */
    public static function get(string $key, string|null $defaultVal = null):?string{
        if(self::$ini === null){
            if (!file_exists(self::$FILE_ENV)) {
                self::$ini = [];
            } else {
                $dat = parse_ini_file(self::$FILE_ENV);
                self::$ini = $dat === false ? [] : $dat;
            }
        }
        return isset(self::$ini[$key]) ? self::$ini[$key] : $defaultVal;
    }

    /** env.iniのキーに記載されている文字列をechoする */
    public static function echo(string $key, string|null $defaultVal = null){
        $v = self::get($key, $defaultVal);
        echo h($v, ENT_QUOTES, Env::get("charset"));
    }

    /* env.iniの[ MODE ]判定 */
    public static function isMode(string $mode_name):bool{
        $m = strtolower(trim(self::get("MODE","")));
        $c = strtolower(trim($mode_name));
        return ($m === $c);
    }
}

/**
 * クライアントからのリクエスト情報を扱うユーティリティクラス
 */
class Request {
    /** @var array<string, mixed> キャッシュ変数 */
    private static $__cache = [];

    public static function route(){
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

        // リクエストURI（例：/subtool/admin/foo/bar）
        $requestUri = $_SERVER['REQUEST_URI'];

        // クエリストリング（?以降）を除外
        $requestPath = parse_url($requestUri, PHP_URL_PATH);

        $route = trim(substr($requestPath, strlen($basePath)), '/');

        if($route === ""){ return SysConf::DEFAULT_ROUTE; }

        return $route;
    }

    /**
     * クライアントのIPアドレスを取得
     * @return string
     */
    public static function ipAddress(): string {  return $_SERVER['REMOTE_ADDR'] ?? ''; }

    /**
     * ユーザーエージェント文字列を取得
     * @return string
     */
    public static function userAgent(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * リクエストメソッド（GET, POSTなど）を取得
     * @return string
     */
    public static function method(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = strtoupper($_SERVER['REQUEST_METHOD']);
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * 使用プロトコルを取得（http:// or https://）
     * @return string
     */
    public static function protocol(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = self::isHttps() ? 'https://' : 'http://';
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * ホスト名を取得
     * @return string
     */
    public static function host(): string { return $_SERVER['HTTP_HOST'] ?? ''; }

    /**
     * フルURLを取得
     * @return string
     */
    public static function url(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = self::protocol() . self::host() . $_SERVER['REQUEST_URI'];
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * クエリを含まない、フルURLを取得
     * @return string
     */
    public static function url_without_query(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = self::protocol() . self::host() . strtok($_SERVER['REQUEST_URI'], '?');
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * アプリケーションのルートURLを取得
     * @return string
     */
    public static function url_root(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = self::protocol() . self::host() . dirname($_SERVER['SCRIPT_NAME']);
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * PATH_INFO を分解して返す
     * @return array<string, string>|null
     */
    public static function path_info(): ?array {
        if (!isset(self::$__cache[__FUNCTION__])) {
            self::$__cache[__FUNCTION__] = null;
            if (!empty($_SERVER['PATH_INFO'])) {
                self::$__cache[__FUNCTION__] = pathinfo($_SERVER['PATH_INFO']);
            }
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * 現在のファイル名（拡張子あり）を取得
     * @return string
     */
    public static function basename(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            $pinfo = self::path_info();
            self::$__cache[__FUNCTION__] = $pinfo['basename'] ?? '';
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * 現在のファイル名（拡張子なし）を取得
     * @return string
     */
    public static function filename(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            $pinfo = self::path_info();
            self::$__cache[__FUNCTION__] = $pinfo['filename'] ?? '';
        }
        return self::$__cache[__FUNCTION__];
    }

    /**
     * 現在のファイルの拡張子を取得
     * @return string
     */
    public static function extension(): string {
        if (!isset(self::$__cache[__FUNCTION__])) {
            $pinfo = self::path_info();
            self::$__cache[__FUNCTION__] = $pinfo['extension'] ?? '';
        }
        return self::$__cache[__FUNCTION__];
    }
    
    /**
     * Cookieから値を取得
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function cookie(string $key, mixed $default = null): mixed {
        return self::_vg($_COOKIE, $key, $default);
    }

    /**
     * セッションから値を取得
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function session(string $key, mixed $default = null): mixed {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $default;
        }
        return self::_vg($_SESSION, $key, $default);
    }

    /**
     * 配列から値を取得（内部用）
     * @param array $dat
     * @param string $key
     * @param mixed $def
     * @return mixed
     */
    private static function _vg(array $dat, string $key, mixed $def): mixed {
        if (!isset($dat[$key])) return $def;
        $r = $dat[$key];
        return ($r === null || $r === '') ? $def : $r;
    }

    public static function isPost(): bool { return self::method() === 'POST'; }
    public static function isGet(): bool { return self::method() === 'GET'; }
    public static function isHead(): bool { return self::method() === 'HEAD'; }

    /**
     * Ajaxリクエストかどうかを判定
     * @return bool
     */
    public static function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Jsonリクエストかどうかを判定
     * @return bool
     */
    public static function isJson(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return (
            str_contains($accept, 'application/json') ||
            strtolower($requestedWith) === 'xmlhttprequest'
        );
    }

    /**
     * クローラー等からのアクセスかを判定
     * @return bool
     */
    public static function isRobot(): bool {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $bots = ['googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandex', 'sogou', 'exabot', 'facebot', 'ia_archiver'];
        foreach ($bots as $bot) {
            if (strpos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function isHttps(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    }

    /**
     * 使用ブラウザ名を推測して返す
     * @return string
     */
    public static function browser(): string {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (strpos($ua, 'Chrome') !== false) return 'Chrome';
        if (strpos($ua, 'Firefox') !== false) return 'Firefox';
        if (strpos($ua, 'Safari') !== false) return 'Safari';
        if (strpos($ua, 'Edge') !== false) return 'Edge';
        if (strpos($ua, 'MSIE') !== false) return 'Internet Explorer';
        return 'Unknown';
    }

    /**
     * 使用OSを推測して返す
     * @return string
     */
    public static function OS(): string {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (strpos($ua, 'Windows NT') !== false) return 'Windows';
        if (strpos($ua, 'Macintosh') !== false) return 'Mac OS';
        if (strpos($ua, 'Linux') !== false) return 'Linux';
        if (strpos($ua, 'Android') !== false) return 'Android';
        if (strpos($ua, 'iPhone') !== false) return 'iOS';
        return 'Unknown';
    }
}

class Response {
    protected static int $statusCode = 200;
    protected static $before = null;
    protected static $after = null;

    public static function beforeSend(?callable $callback){
        self::$before = $callback;
    }

    public static function afterSend(?callable $callback){
        self::$after = $callback;
    }
    protected static function callHook(?callable $hook): void {
        if (is_callable($hook)) {
            try {
                $hook();
            } catch (\Throwable $e) {
                // ログ出力やエラー通知など
            }
        }
    }

    public static function setStatusCode(int $code) {
        self::$statusCode = $code;
        http_response_code($code);
    }
    public static function getStatusCode(): int {
        return self::$statusCode;
    }

    public static function view(string $template, array $data = []) {
        if(! Render::hasTemplate($template)){ return self::error(500);  }
        self::callHook(self::$before);

        $content = Render::getTemplate($template, $data);

        Render::showHeader();
        Render::showMessage();
        echo $content;
        Render::showToast();
        Render::showFooter();

        self::callHook(self::$after);
    }

    public static function partialView(string $template, array $data = []){
        if(! Render::hasTemplate($template)){ return self::error(500);  }
        self::callHook(self::$before);

        $content = Render::getTemplate($template, $data);

        Render::showMessage();
        echo $content;
        Render::showToast();
        self::callHook(self::$after);
    }

    public static function error(int $code, ?string $message = null): never {
        self::setStatusCode($code);
        self::callHook(self::$before);

        
        if (Request::isJson()) {
            echo json_encode(['error' => $message, 'code' => $code]);
            exit;
        }
        $content = "";
        if(Render::hasTemplate($code)){
            $content = Render::getTemplate($code, ["message" => $message]);
        }else{
            $content = "<h1>Error {$code}</h1>";
            if ($message) { $content .= "<p>" . h($message) . "</p>"; }
        }
        Render::showHeader();
        echo $content;
        Render::showFooter();

        self::callHook(self::$after); exit;
    }

    /**
     * リダイレクト
     * @param string $url
     * @return never
     */
    public static function redirect(string $url): never {
        self::callHook(self::$before);
        header("Location: $url", true, 303);
        self::callHook(self::$after); exit;
    }

    /**
     * No Content (204) レスポンス
     * @return never
     */
    public static function noContent(): never {
        self::setStatusCode(204);
        self::callHook(self::$before);
        self::callHook(self::$after); exit;
    }

    /**
     * JSON形式でレスポンス
     * @param array|object $data
     * @param string|null $dl_filename
     * @return never
     */
    public static function json(array|object $data, ?string $dl_filename = null): never {
        if ($dl_filename === null) $dl_filename = Request::basename();
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            self::setStatusCode(500);
            self::callHook(self::$before);
            echo 'JSON ENCODE ERROR: ' . json_last_error_msg();
        } else {
            self::setStatusCode(200);
            self::callHook(self::$before);
            self::_hd($dl_filename);
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
        }
        self::callHook(self::$after); exit;
    }

    /**
     * プレーンテキストで出力
     * @return never
     */
    public static function text(string $content, ?string $dl_filename = null): never {
        if ($dl_filename === null) $dl_filename = Request::basename();
        self::setStatusCode(200);
        self::callHook(self::$before);
        self::_hd($dl_filename);
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;

        self::callHook(self::$after); exit;
    }

    /**
     * ファイルをダウンロードさせる
     * @param string $path
     * @param string|null $dl_filename
     * @return never
     */
    public static function file(string $path, ?string $dl_filename = null): never {
        if (is_file($path)) {
            self::callHook(self::$before);
            if ($dl_filename === null) $dl_filename = pathinfo($path)["basename"];
            self::_hd($dl_filename);
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($path));
            while (ob_get_level()) ob_end_clean();
            ob_start();
            if ($fp = fopen($path, 'rb')) {
                try {
                    while (!feof($fp) && connection_status() === 0) {
                        echo fread($fp, 1024);
                        ob_flush();
                        flush();
                    }
                } catch (\Exception) { }
                fclose($fp);
            }
            ob_end_clean();
        } else {
            self::setStatusCode(404);
            self::callHook(self::$before);

        }
        self::callHook(self::$after); exit;
    }
    public static function download(string $path, ?string $dl_filename = null): never {
        self::file($path, $dl_filename);
    }

    /**
     * ダウンロードヘッダ出力（内部用）
     * @param string $dl_filename
     * @return void
     */
    protected static function _hd(string $dl_filename): void {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (strpos($ua, 'MSIE') !== false || strpos($ua, 'Trident') !== false) {
            header('Content-Disposition: attachment; filename="' . rawurlencode($dl_filename) . '"');
        } else {
            header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($dl_filename));
        }
    }
}

class Render {
    protected static ?string $title = null;
    protected static Form  $form;
    protected static array $datas = [];
    protected static array $menus = [];
    protected static array $messages = [];
    protected static array $toasts = [];

    // === TEMPLATE ===
    public static function showTemplate(string $template){
        echo self::getTemplate($template);
    }
    public static function getTemplate(string $template){
        ob_start();
        include Path::layout($template . ".php");
        return ob_get_clean();
    }
    public static function hasTemplate(string $template){
        $file = Path::layout("{$template}.php");
        return file_exists($file);
    }
    public static function showHeader(){
        include Path::layout(SysConf::TEMPLATE_HEADER);
    }
    public static function showFooter(){
        include Path::layout(SysConf::TEMPLATE_FOOTER);
    }
    // === TITLE ===
    public static function setTitle(?string $title) {
        self::$title = $title;
    }
    public static function showTitle() {
        echo h(self::$title);
    }
    public static function showTitleTag() {
        echo "<title>";
        if (empty(self::$title)){
            echo h(SysConf::SITE_NAME);
        }else{
            echo h(self::$title . ' ' . Env::get("delimiter", "-"). ' ' . SysConf::SITE_NAME);
        }
        echo "</title>";
    }
    public static function setForm(Form $form, bool $addMessage = true){
        self::$form = $form;
        if($addMessage){
            self::addMessage($form->getErrors(), "error");
        }
    }
    public static function getForm():Form{
        if(self::$form === null){ self::$form = new Form(); }
        return self::$form;
    }

    public static function setData(string $key, mixed $val){
        self::$datas[$key] = $val;
    }
    public static function getData(string $key, mixed $default = null):mixed{
        if(isset(self::$datas[$key])){
            return self::$datas[$key];
        }
        return $default;
    }

    // === MENU ===
    public static function setMenu(array $menus, string $key = 'nav') {
        self::$menus[$key] = $menus;
    }
    public static function showMenu(string $key = 'nav') {
        if (!isset(self::$menus[$key])) return;
        echo '<ul class="' . h($key) . '">';
        foreach (self::$menus[$key] as $v) {
            echo '<li><a href="' . Url::root($v['page']) . '">' . h($v['title']) . '</a></li>';
        }
        echo '</ul>';
    }
    // === MESSAGES ===
    public static function addMessage(string|array $msg, string $key = 'error') {
        self::addToGroup(self::$messages, $msg, $key);
    }

    public static function clearMessages() {
        self::$messages = [];
    }

    public static function showMessage(): void {
        self::renderList(self::$messages, "messages");
    }

    // === TOAST ===
    public static function addToast(string|array $msg, string $key = 'error') {
        self::addToGroup(self::$toasts, $msg, $key);
    }

    public static function clearToasts() {
        self::$toasts = [];
    }

    public static function showToast(): void {
        self::renderList(self::$toasts, "toasts");
    }

    // === 共通内部関数 ===
    protected static function addToGroup(array &$target, string|array $msg, string $key): void {
        if (empty($msg)) return;
        foreach ((array)$msg as $m) {
            $target[$key][] = $m;
        }
    }
    protected static function renderList(array $items, string $wrapperClass = "messages"): void {
        foreach ($items as $key => $msgs) {
            echo '<ul class="' . h($wrapperClass . ' ' . $key) . '">';
            foreach ($msgs as $m) {
                echo '<li>' . h($m) . '</li>';
            }
            echo '</ul>';
        }
    }
}
class Cookie {
    public static function set(string $key, string $value, int $expire = 0, string $path = "/", string $domain = "", bool $secure = false, bool $httponly = true): void {
        setcookie($key, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax',
        ]);
    }

    public static function get(string $key): ?string {
        return $_COOKIE[$key] ?? null;
    }

    public static function delete(string $key): void {
        self::set($key, '', time() - 3600);
    }
}
class Session {
    protected static bool $started = false;

    public static function start(): void {
        if (!self::$started && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            self::$started = true;
        }
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        self::start();
        session_destroy();
    }
}


class Auth {
    public static $LOGIN_PAGE = null;

    protected static ?string $sessionKey = 'auth_user_id@'.__DIR__;

    public static function login(string $user_id): void {
        $_SESSION[self::$sessionKey] = $user_id;
    }

    public static function logout(): void {
        unset($_SESSION[self::$sessionKey]);
    }

    public static function check(): bool {
        return isset($_SESSION[self::$sessionKey]);
    }

    public static function user(): ?string {
        return $_SESSION[self::$sessionKey] ?? null;
    }

    public static function requireLogin(){

        if(! self::check()){
            if(self::$LOGIN_PAGE === null){
                self::$LOGIN_PAGE = Env::get("LOGIN_PAGE", "login");
            }
            header('Location: ' . Url::root(self::$LOGIN_PAGE));
            exit;
        }
    }
}

/** モデルデータを扱う抽象クラス */
abstract class Model{
    protected static $_f_names = null;
    /** インスタンスの作成 */
    public static function getInstance(mixed $data = null):static{
        return new static($data);
    }
    /** フィールド名(public)の一覧を取得する */
    public static function getFieldNames(): array {
        if (isset(self::$_f_names[static::class])) { return self::$_f_names[static::class]; }
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
        $ret = array();
        foreach ($props as $p) { if ($p->isPublic()) { $ret[] = $p->getName(); } }
        self::$_f_names[static::class] = $ret;
        return self::$_f_names[static::class];
    }

    function __construct(mixed $data = null){
        if(empty($data)){ $this->clear(); $this->__after_construct(); return;}
        if(is_array($data)){
            $this->setArray($data);
        }elseif($data instanceof Model){
            $this->setArray($data->getArray());
        }elseif(is_string($data)){
            $this->setJson($data);
        }
        $this->__after_construct();
    }
    protected function __after_construct(){ }

    /** <p>クローン</p> */
    public function createClone():Model{ return static::getInstance($this->getArray()); }

    /** <p>情報をクリア</p> */
    public function clear(){ $props = static::getFieldNames(); foreach($props as $p){ $this->$p = null; } }

    /** 全てのフィールドがNULL or 空文字の場合はtrueを返す */
    public function isEmpty():bool{
        $fs = static::getFieldNames();
        foreach($fs as $field){
            if ($this->$field !== null && $this->$field !== "") {
                return false;
            }
        }
        return true;
    }

    /** フィールドのデータを取得。存在しない場合やNULL空文字の場合はdefault_valを返す */
    public function getValue(string $fieldName, $default_val = null):mixed{
        if(! in_array($fieldName, static::getFieldNames())){ return $default_val; }
        $x = $this->$fieldName;
        if($x === null || $x === ""){ $x =$default_val;}
        return $x;
    }
    /** フィールドにデータをセット */
    public function setValue(string $fieldName, $val){
        if(! in_array($fieldName, static::getFieldNames())){ return; }
        $this->$fieldName = $val;
    }

    /** 配列としてデータを取得 */
    public function getArray():array{
        $ret = array();
        $props = static::getFieldNames();
        foreach($props as $p){
            $v =  $this->$p;
            $ret[$p] = $v; 
        }
        return $ret;
    }
    /** 特定のフィールドのみを取得 */
    public function selectArray(string ...$fieldNames):array{
        $ret = array();
        if(empty($fieldNames)){return $ret;}
        $props = static::getFieldNames();
        foreach($props as $p){
            if(in_array($p, $fieldNames)){
                $ret[$p] = $this->$p;
            }
        }
        return $ret;
    }
    /** 特定のフィールドのみを取得 */
    public function selectJson(string ...$fieldNames):string{
        return json_encode($this->selectArray(...$fieldNames), JSON_UNESCAPED_UNICODE);
    }
    /** 特定のフィールドのみを取得 */
    public function selectModel(string ...$fieldNames):Model{
        return static::getInstance($this->selectArray(...$fieldNames));
    }

    /** clear実行しデータをセットする */
    public function setArray(array $data){
        $this->clear();
        $this->mergeArray($data);
    }
    /** データを自身にマージする */
    public function mergeArray(array $other){
        if(empty($other)){ return; }
        $props = static::getFieldNames();
        foreach($props as $p){
            if(!isset($other[$p])){ continue; }
            $this->$p = $other[$p];
        }
    }
    /** <p>Jsonとしてデータを取得</p> */
    public function getJson():string{
        return json_encode($this->getArray(), JSON_UNESCAPED_UNICODE);
    }
    /** clear実行しデータをセットする */
    public function setJson(string $json){
        $this->setArray(json_decode($json, true));
    }
    /** データを自身にマージする */
    public function mergeJson(string $json){
        $this->mergeArray(json_decode($json, true));
    }
    /** clear実行しデータをセットする */
    public function setModel(Model $data){
        $this->setArray($data->getArray());
    }
    /** データを自身にマージする */
    public function mergeModel(Model $data){
        $this->mergeArray($data->getArray());
    }
}

/** ディレクトリIOクラス */
class DirectoryInfo {
    protected $full = null;
    protected $info = null;
    private $exists = null;

    protected function gi($n, $nv = ""): string {
        if ($this->info === null) return $nv;
        if (isset($this->info[$n])) return $this->info[$n];
        return $nv;
    }

    /** コンストラクタ */
    public function __construct($fullname) {
        $this->full = self::normalize($fullname);
        $this->info = pathinfo($this->full);
        if($fullname === null || $fullname === "" || $fullname === DIRECTORY_SEPARATOR){ $this->exists = false; }
    }

    /** 存在確認 */
    public function exists(bool $use_cache = true): bool {
        if ($use_cache === false || $this->exists === null) {
            $this->exists = file_exists($this->fullName()) && is_dir($this->fullName());
        }
        return $this->exists;
    }

    /** フルパス名を取得 */
    public function fullName(): string {
        return $this->full;
    }

    /** ディレクトリ名を取得 */
    public function name(): string {
        return $this->gi("basename");
    }

    /** 親フォルダの情報を取得 */
    public function parentDirectory():string{ return $this->gi("dirname"); }
    /** 親フォルダの情報を取得 */
    public function parentDirectoryInfo():DirectoryInfo{ return new DirectoryInfo($this->parentDirectory()); }

    /** ファイルPathの一覧取得 */
    public function getFilePaths(...$ptrns): array {
        $ret = array();
        if (!$this->exists()) {
            return $ret;
        }
        if (empty($ptrns)) {
            foreach (glob($this->full . DIRECTORY_SEPARATOR . "{*,.[!.]*,..?*}", GLOB_BRACE) as $f) {
                if (is_file($f)) {
                    $ret[] = $f;
                }
            }
        } else {
            foreach ($ptrns as $p) {
                foreach (glob($this->full . DIRECTORY_SEPARATOR . $p, GLOB_BRACE) as $f) {
                    if (is_file($f)) {
                        $ret[] = $f;
                    }
                }
            }
        }
        return $ret;
    }

    /** ファイル名(pathを含まない)の一覧を取得 */
    public function getFileNames(...$ptrns): array {
        $ret = array();
        foreach ($this->getFilePaths(...$ptrns) as $v) {
            $p = pathinfo($v);
            $ret[] = $p["basename"];
        }
        return $ret;
    }

    /**
     * ファイルInfoの一覧を取得 
     * @param [type] ...$ptrns
     * @return array<FileInfo>
     */
    public function getFileInfos(...$ptrns): array {
        $ret = array();
        foreach ($this->getFilePaths(...$ptrns) as $v) {
            $ret[] = new FileInfo($v);
        }
        return $ret;
    }

    /** ファイルPathを取得 */
    public function getFilePath($childName): string {
        return $this->full.DIRECTORY_SEPARATOR.$childName;
    }

    /** ファイルInfoを取得 */
    public function getFileInfo($childName): FileInfo {
        return new FileInfo($this->getFilePath($childName));
    }

    /**
     * ディレクトリの一覧を取得 
     * @param [type] ...$ptrns
     * @return array<string>
     */
    public function getDirectoryPaths(...$ptrns): array {
        $ret = array();
        if (!$this->exists()) {
            return $ret;
        }
        if (empty($ptrns)) {
            foreach (glob($this->full . DIRECTORY_SEPARATOR . "{*,.[!.]*,..?*}", GLOB_BRACE | GLOB_ONLYDIR) as $f) {
                if (is_dir($f)) {
                    $ret[] = $f;
                }
            }
        } else {
            foreach ($ptrns as $p) {
                foreach (glob($this->full . DIRECTORY_SEPARATOR . $p, GLOB_BRACE | GLOB_ONLYDIR) as $f) {
                    if (is_dir($f)) {
                        $ret[] = $f;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * ディレクトリInfoの一覧を取得 
     * @param [type] ...$ptrns
     * @return array<static>
     */
    public function getDirectoryInfos(...$ptrns): array {
        $ret = array();
        foreach ($this->getDirectoryPaths(...$ptrns) as $v) {
            $ret[] = new DirectoryInfo($v);
        }
        return $ret;
    }

    /** 子ディレクトリのフルPathを取得 */
    public function getDirectoryPath($childName): string {
        return $this->full . DIRECTORY_SEPARATOR . $childName;
    }

    /** 子ディレクトリのInfoを取得 */
    public function getDirectoryInfo($childName): DirectoryInfo {
        return new DirectoryInfo($this->full.DIRECTORY_SEPARATOR.$childName);
    }

    /** ディレクトリが存在しない場合作成します（再帰的に削除するかを指定できます） */
    public function make($mode = 0777, $recursive = false) {
        if (empty($this->full) || $this->exists()) return;
        if($recursive){
            // 親ディレクトリの再帰的作成
            $parentDir = new DirectoryInfo($this->parentDirectory());
            $parentDir->make($mode, true);
        }
        mkdir($this->full, $mode, true);
        $this->exists(false);
    }

    /** ディレクトリを削除します（再帰的に削除するかを指定できます） */
    public function delete() {
        if (!$this->exists()) return;
        foreach ($this->getDirectoryInfos() as $i) $i->delete();
        foreach ($this->getFileInfos() as $i) $i->delete();
        if(! rmdir($this->full) ){
            throw new \Exception("Failed to remove directory : '{$this->full}'.");
        }
        $this->exists(false);
    }

    /** リネーム（DIRECTORY_SEPARATORを含まない場合同一ディレクトリで名前のみ変更） */
    public function rename(string $newname):bool{
        $to = str_replace("\\", "/", $newname);
        if(strpos($to, "/") === false){
            $parent = $this->parentDirectory()."/";
            $to = $parent.$to;
        }
        $to = str_replace("/", DIRECTORY_SEPARATOR, $to);
        return rename($this->fullName(), $to);
    }

    /** コピー */
    public function copyTo($destination, $overwrite = false) {
        if (!$this->exists()) return;
        if (!is_dir($destination)) {
            $destinationDir = new DirectoryInfo($destination);
            $destinationDir->make();
        }

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true); // ディレクトリを作成
        }

        $files = $this->getFilePaths();
        foreach ($files as $file) {
            $filename = basename($file);
            $destinationFile = $destination . DIRECTORY_SEPARATOR . $filename;
            if(file_exists($destinationFile)){
                if($overwrite){
                    unlink($destinationFile);
                }else{
                    continue;
                }
            }
            copy($file, $destinationFile);
        }

        $dirInfos = $this->getDirectoryInfos();
        foreach($dirInfos as $di){
            $destination_next = $destination . DIRECTORY_SEPARATOR . $di->name();
            $di->copyTo($destination_next, $overwrite);
        }
    }

    /** 移動 */
    public function moveTo($destination, $overwrite = false) {
        $this->copyTo($destination, $overwrite);
        $this->delete();
    }

    /** 作成日時を取得 */
    public function getCreatedTime(?string $format = null): null|string|int {
        if (!$this->exists()) return null;
        $r = filectime($this->fullName());
        if($format === null){
            return $r;
        }
        return date($format, $r);
    }

    /** 更新日時を取得 */
    public function getUpdatedTime(?string $format = null): null|string|int {
        if (!$this->exists()) return null;
        $r = filemtime($this->fullName());
        if($format === null){
            return $r;
        }
        return date($format, $r);
    }

    /** ディレクトリが空かどうかを判定 */
    public function isEmpty(): bool {
        if (!$this->exists()) {
            return true;
        }

        $files = glob($this->full . DIRECTORY_SEPARATOR . "{*,.[!.]*,..?*}", GLOB_BRACE);
        return count($files) === 0;
    }

    /** 正規化する。/../や/./などを削除 */
    protected static function normalize(string $path):string{
        $url = str_replace('\\', '/', $path);
        if(strpos($url, '/../') === false && strpos($url,'/./') === false){
            $r = $url;
        }else{
            $parts = explode('/', $url);
            $resolvedParts = array();
            foreach ($parts as $part) {
                if($parts === ''){ continue; }
                if ($part === '..') {
                    array_pop($resolvedParts);
                } elseif ($part !== '.') {
                    $resolvedParts[] = $part;
                }
            }
            $r = implode('/', $resolvedParts);
        }
        if(DIRECTORY_SEPARATOR !== '/'){
            $r = str_replace('/', DIRECTORY_SEPARATOR, $r);
        }

        return rtrim($r, DIRECTORY_SEPARATOR);
    }
}

/** ファイルIOクラス */
class FileInfo  {
    protected $full = null;
    protected $info = null;
    private $exists = null;

    protected function gi($n, $nv = ""): string {
        if ($this->info === null) return $nv;
        if (isset($this->info[$n])) return $this->info[$n];
        return $nv;
    }

    /** コンストラクタ */
    public function __construct($fullname) {
        $this->full = self::normalize($fullname);
        $this->info = pathinfo($this->full);
        if($fullname === null || $fullname === "" || $fullname === DIRECTORY_SEPARATOR){ $this->exists = false; }
    }

    /** 存在確認 */
    public function exists(bool $use_cache = true): bool {
        if($use_cache === false || $this->exists === null){
            $this->exists = file_exists($this->fullName()) && is_file($this->fullName());
        }
        return $this->exists;
    }

    /** フルパス名を取得 */
    public function fullName(): string {
        return $this->full;
    }

    /** ファイル名を取得 */
    public function name($needExtention = true): string {
        if ($needExtention) return $this->gi("basename");
        return basename($this->fullName(), $this->extension(TRUE));
    }

    /** 親フォルダの情報を取得 */
    public function parentDirectory():string{ return $this->gi("dirname"); }
    /** 親フォルダの情報を取得 */
    public function parentDirectoryInfo():DirectoryInfo{ return new DirectoryInfo($this->parentDirectory()); }

    /** 拡張子を取得 */
    public function extension($needDot = true): string {
        return ($needDot ? "." : "") . $this->gi("extension");
    }

    /** ファイル情報を文字列として取得。存在しない場合はNULLを返す */
    public function getText(): ?string {
        if (!$this->exists()) return null;
        return file_get_contents($this->fullName());
    }
    /** ファイルへ情報を書き込みます */
    public function putText($text, $lock = true){
        if (empty($this->full)) return;
        $bs = $this->parentDirectoryInfo();
        if (!$bs->exists()) $bs->make();
        $opt = (($lock === true) ? LOCK_EX : 0);
        file_put_contents($this->fullName(), $text, $opt);
        $this->exists(false);
    }
    /** ファイルを削除します */
    public function delete() {
        if ($this->exists()) return;
        unlink($this->fullName());
        $this->exists(false);
    }

    /** ファイルを別の場所にコピーします<br>コピー先に同名のファイルがすでに存在する場合は失敗 */
    public function copyTo($destination, $overwrite = false): bool {
        if (!$this->exists()) return false;

        if(file_exists($destination)){
            if($overwrite){
                unlink($destination);
            }else{
                return false;
            }
        }
        return copy($this->fullName(), $destination);
    }

    /** ファイルを別の場所に移動します<br>移動先に同名のファイルがすでに存在する場合は失敗 */
    public function moveTo($destination): bool {
        if (!$this->exists()) return false;

        if (file_exists($destination)) return false; // 移動先に同名のファイルがすでに存在する場合は失敗
        return rename($this->fullName(), $destination);
    }
    /** ファイルのサイズ（バイト単位）を取得します */
    public function getSize(): ?int {
        if (!$this->exists()) return null;
        return filesize($this->fullName());
    }

    /** ファイルの作成日時を取得します */
    public function getCreatedTime(?string $format = null): null|string|int {
        if (!$this->exists()) return null;
        $r = filectime($this->fullName());
        if($format === null){
            return $r;
        }
        return date($format, $r);
    }
    /** ファイルの更新日時を取得します */
    public function getUpdatedTime(?string $format = null): null|string|int {
        if (!$this->exists()) return null;
        $r = filemtime($this->fullName());
        if($format === null){
            return $r;
        }
        return date($format, $r);
    }

    /** sha1ハッシュを取得。存在しない場合はNULLを返す */
    public function hash(): ?string {
        if (!$this->exists()) return null;
        return sha1_file($this->fullName());
    }

    /** 正規化する。/../や/./などを削除 */
    protected static function normalize(string $path):string{
        $url = str_replace('\\', '/', $path);
        if(strpos($url, '/../') === false && strpos($url,'/./') === false){
            $r = $url;
        }else{
            $parts = explode('/', $url);
            $resolvedParts = array();
            foreach ($parts as $part) {
                if($parts === ''){ continue; }
                if ($part === '..') {
                    array_pop($resolvedParts);
                } elseif ($part !== '.') {
                    $resolvedParts[] = $part;
                }
            }
            $r = implode('/', $resolvedParts);
        }
        if(DIRECTORY_SEPARATOR !== '/'){
            $r = str_replace('/', DIRECTORY_SEPARATOR, $r);
        }
        return $r;
    }
}

/** <p>物理パス関連</p> */
class Path{
    /** <p> [ ABSPATH ] からのPathを返す</p> */
    public static function root(... $add_path):string{ return self::_g(ABSPATH, $add_path); }

    public static function app(... $add_path):string{ return self::_g(self::root(SysConf::DIR_APP), $add_path); }
    public static function page(... $add_path):string{ return self::_g(self::root(SysConf::DIR_PAGE), $add_path); }
    public static function layout(... $add_path):string{ return self::_g(self::root(SysConf::DIR_LAYOYT), $add_path); }
    public static function public(... $add_path):string{ return self::_g(self::root(SysConf::DIR_PUBLIC), $add_path); }
    public static function temp(... $add_path):string{
        $d = self::root(Env::get("DIR_TMP", ".tmp"));
         if(!is_dir($d)){
            if (!mkdir($d, 0766, true)) {
                // エラーハンドリング（例：ログ出力や例外）
                die("Failed to create directory: $d");
            }
            file_put_contents($d.DIRECTORY_SEPARATOR.".htaccess", "Deny from all");
        }
        return self::_g($d, $add_path);
    }

    /** <p>Pathの結合</p> */
    public static function combine(...$paths):string{
        $ret = ''; $flg = false;
        $fst = null;
        foreach($paths as $p){
            if(is_array($p)){
                $p = self::combine(...$p);
            }
            $rep = str_replace('\\', '/', trim($p));
            if($fst === null){
                $fst = $rep;
            }

            $p =  trim($rep, '/');
            if ($p === '') { continue; }
            if($flg){ $ret .= '/'; }else{ $flg = true; }
            $ret .= $p;
        }

        if($fst !== null && $fst !== "" && $fst[0] === '/'){
            $ret = '/'.$ret;
        }
        return self::normalize($ret);
    }

    /** 正規化する。/../や/./などを削除 */
    public static function normalize(string $path): string {
        $url = str_replace('\\', '/', $path);
    
        // Windows ドライブレター判定（C:/やD:/など）
        $drivePrefix = '';
        if (preg_match('#^([a-zA-Z]:)(/|$)#', $url, $matches)) {
            $drivePrefix = $matches[1];
            $url = substr($url, strlen($drivePrefix)); // ドライブ名を除いたパスにする
        }
    
        $isAbsolute = str_starts_with($url, '/');
        $parts = explode('/', $url);
        $resolvedParts = [];
    
        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            } elseif ($part === '..') {
                if (!empty($resolvedParts) && end($resolvedParts) !== '..') {
                    array_pop($resolvedParts);
                } else {
                    $resolvedParts[] = '..';
                }
            } else {
                $resolvedParts[] = $part;
            }
        }
    
        $normalized = implode('/', $resolvedParts);
    
        if ($isAbsolute) {
            $normalized = '/' . $normalized;
        }
        if ($drivePrefix !== '') {
            $normalized = $drivePrefix . ($normalized !== '' ? '/' . ltrim($normalized, '/') : '');
        }
    
        // OSに合わせてスラッシュを変換
        if (DIRECTORY_SEPARATOR !== '/') {
            $normalized = str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        }
    
        return $normalized;
    }

    private static function _g($base,  $add_path):string{
        if(empty($add_path)){ return self::normalize($base); }
        $params = $add_path;
        array_unshift($params, $base);
        return self::combine( ... $params );
    }
}

/** <p>URLパス関連</p> */
class Url{
    /** <p> [ site_url() ] からのURLを返す</p> */
    public static function root(... $add_path):string{ return self::_g(Request::url_root() , $add_path); }

    public static function public(... $add_path):string{ return self::_g(self::root(SysConf::DIR_PUBLIC), $add_path); }

    /** <p>Pathの結合</p> */
    public static function combine(... $paths):string{
        $ret = '';
        $flg = false;
        foreach($paths as $p){
            $p = trim($p, '/');
            if ($p === '') { continue; }
            if($flg){ $ret .= '/'; }else{ $flg = true; }
            $ret .= $p;
        }
        //正規化
        return self::normalize($ret);
    }
    /** URLを正規化する。/../や/./などを削除 */
    public static function normalize(string $url):string{
        if(strpos($url, '/../') === false && strpos($url,'/./') === false){
            return $url;
        }else{
            $parts = explode('/', $url);
            $resolvedParts = array();
            foreach ($parts as $part) {
                if ($part === '..') {
                    array_pop($resolvedParts);
                } elseif ($part !== '.') {
                    $resolvedParts[] = $part;
                }
            }
            return implode('/', $resolvedParts);
        }
    }
    private static function _g($base,  $add_path):string{
        if(empty($add_path)){ return self::normalize($base); }
        $params = $add_path;
        array_unshift($params, $base);
        return self::combine( ... $params );
    }
}

//======================================================================================
class ZipUtil{
    public static $errorInfo = null;

    private static $ignoreDirs = array();
    private static $ignoreFiles = array();
    private static $ignoreRelaDirs = array();
    private static $ignoreRelaFiles = array();

    public static function unZip($zipFile, $openDir):bool{
        try{
            if(!is_file($zipFile)){ return false; }
            if(empty($openDir)) { return false; }
            if(!is_dir($openDir)){ mkdir($openDir, 0777); }
            
            set_time_limit(0);

            $zip = new \ZipArchive();
            if( $zip->open($zipFile) === true){
                $zip->extractTo($openDir);
                $zip->close();
            }
            return true;
        }catch(\Exception $ex){
            self::$errorInfo = "ERROR: ".$ex->getMessage();
        }
        return false;
    }
    
    public static function toZip(string $sourceDir, string $outputZipFile, ?array $ignoreDirs = null, ?array $ignoreFiles = null):bool{
        try{
            if(!file_exists($sourceDir) ){ self::$errorInfo = "Not found sourceDir"; return false; }
            if(empty($outputZipFile)) { self::$errorInfo = "outputZipFile is Empty";  return false; }

            $sourceDir = rtrim(str_replace("\\", "/", $sourceDir), "/");
            $outputZipFile = str_replace("\\", "/", $outputZipFile);
            self::setIgnores($ignoreDirs, $ignoreFiles);

            set_time_limit(0);
            
            $result = array();
            self::preZip($result, $sourceDir, "");

            if(!empty($result)){
                $zip = new \ZipArchive();
                if($zip->open($outputZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true ){
                    self::addZip($zip, $result, $sourceDir, null);
                    $zip->close();
                    return true;
                }
            }
            self::$errorInfo = "Failed to open the ZIP archive";
            return false;
        }catch(\Exception $ex){
            self::$errorInfo = "ERROR: ".$ex->getMessage();
        }
        return false;
    }
    
    private static function preZip(array &$result, $absolute_path, $relative_path){
        if(!file_exists($absolute_path)){ return false; }
        if(is_dir($absolute_path)){
            $fs = scandir($absolute_path);
            foreach($fs as $f){
                if(empty($f) || $f == "." || $f == ".."){ continue; }
                $path = $absolute_path."/".$f;
                $relative = $relative_path."/".$f;

                if(is_file($path)){
                    //FILE
                    if(self::cIgnoreFile($f)) { continue; }
                    if(self::cIgnoreRelaFile($relative)) { continue; }

                    $result[] = $f;

                }elseif(is_dir($path)){
                    //DIR
                    if(self::cIgnoreDir($f)) { continue; }
                    if(self::cIgnoreRelaDir($relative)) { $relative;continue; }

                    $key = "/".$f;
                    $result[$key] = array();
                    self::preZip($result[$key], $path, $relative);
                }
            }

        }elseif(is_file($absolute_path)){
            //case: single file
            $info = pathinfo($absolute_path);
            $name = $info["basename"];
            $result[] = $name;
        }
        return true;
    }
    private static function addZip(\ZipArchive &$zip, array &$result, string $sourceDir, ?string $dir = null){
        if(empty($result)){ return; }
        foreach($result as $k => $v){
            if(!is_int($k)){
                //dir
                if($dir === null){
                    $relative = substr($k,1);
                }else{
                    $relative = $dir.$k;
                }
                $zip->addEmptyDir($relative);
                self::addZip($zip, $v, $sourceDir, $relative);
            }else{
                //file
                if($dir === null){
                    $relative = $v;
                }else{
                    $relative = $dir."/".$v;
                }
                $file = $sourceDir."/".$relative;
                $zip->addFile($file, $relative);
            }
        }
    }

    private static function setIgnores(null|array $ignoreDirs, null|array $ignoreFiles){
        self::$ignoreDirs = array();
        self::$ignoreRelaDirs = array();
        self::$ignoreFiles = array();
        self::$ignoreRelaFiles = array();
        if(!empty($ignoreDirs)){
            foreach($ignoreDirs as $n){ $n = trim($n); if($n===null||$n===""){ continue; }
                $n = str_replace("\\","/",$n);
                if(strpos($n, "/")===false){
                    self::$ignoreDirs[] = $n;
                }else{
                    self::$ignoreRelaDirs[] = $n;
                }
            }
        }
        if(!empty($ignoreFiles)){
            foreach($ignoreFiles as $n){ $n = trim($n); if($n===null||$n===""){ continue; }
                $n = str_replace("\\","/",$n);
                if(strpos($n, "/")===false){
                    self::$ignoreFiles[] = $n;
                }else{
                    self::$ignoreRelaFiles[] = $n;
                }
            }
        }
    }
    private static function cIgnoreRelaFile($name){ foreach (self::$ignoreRelaFiles as $p) { if (fnmatch($p, $name)) { return true; } } return false; }
    private static function cIgnoreRelaDir($name){ foreach (self::$ignoreRelaDirs as $p) { if (fnmatch($p, $name)) { return true; } } return false; }
    private static function cIgnoreFile($name){ foreach (self::$ignoreFiles as $p) { if (fnmatch($p, $name)) { return true; } } return false; }
    private static function cIgnoreDir($name){ foreach (self::$ignoreDirs as $p) { if (fnmatch($p, $name)) { return true; } } return false; }
}

// ===================================================================
/**
 * SQLite専用のPDOラッパークラス。
 * 主にトランザクション処理、SELECT/INSERT/UPDATEの簡易化、エンティティの保存などに対応。
 */
class SqliteCon {
    /** @var array<string, array<string>> キャッシュされたクラスごとの公開プロパティ名 */
    private static $C_FLD;

    /** @var \PDO SQLite接続インスタンス */
    private $db;

    /**
     * SQLiteファイルに接続する
     *
     * @param string $filename SQLiteファイルのパス
     */
    public function __construct(string $filename) {
        $db = new \PDO("sqlite:" . $filename);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db = $db;
    }

    /**
     * トランザクションを開始する
     *
     * @return bool
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    /**
     * トランザクションをロールバックする
     *
     * @return bool
     */
    public function rollBack(): bool {
        return $this->db->rollBack();
    }

    /**
     * トランザクションをコミットする
     *
     * @return bool
     */
    public function commit(): bool {
        return $this->db->commit();
    }

    /**
     * 直近のINSERTのIDを取得する
     *
     * @return int
     */
    public function lastInsertId(): int {
        return $this->db->lastInsertId();
    }

    /**
     * クエリ結果を配列として取得する
     *
     * @template T
     * @param string $query 実行するSQL
     * @param array|null $params バインドするパラメータ
     * @param class-string<T>|null $fetchClass 取得結果をマッピングするクラス名（オプション）
     * @return array<array<string, mixed>>|array<T>
     */
    public function select(string $query, ?array $params = null, ?string $fetchClass = null): array {
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        if ($fetchClass === null) {
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            if (class_exists($fetchClass)) {
                $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $fetchClass);
            } else {
                $stmt->setFetchMode(\PDO::FETCH_OBJ);
            }
        }
        return $stmt->fetchAll();
    }

    /**
     * クエリ結果の最初の1件だけを取得する
     *
     * @template T
     * @param string $query 実行するSQL
     * @param array|null $params バインドするパラメータ
     * @param class-string<T>|null $fetchClass 結果をマッピングするクラス（オプション）
     * @return T|array<string, mixed>|null
     */
    public function get(string $query, ?array $params = null, ?string $fetchClass = null): mixed {
        $r = $this->select("SELECT * FROM (" . $query . ") Q LIMIT 1;", $params, $fetchClass);
        return count($r) === 0 ? null : $r[0];
    }

    /**
     * SQLを実行し、成功可否を返す
     *
     * @param string $query 実行するSQL
     * @param array|null $params バインドするパラメータ
     * @return bool
     */
    public function exec(string $query, ?array $params = null): bool {
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_STR);
            }
        }
        return $stmt->execute();
    }

    /**
     * サブクエリの件数を取得する
     *
     * @param string $query カウント対象のサブクエリ
     * @param array|null $params バインドするパラメータ
     * @return int
     */
    public function count(string $query, ?array $params = null): int {
        $r = $this->select("SELECT COUNT(0) AS C FROM (" . $query . ") Q;", $params);
        return (int) $r[0]["C"];
    }

    /**
     * 配列データをテーブルに保存する（INSERT or UPDATE）
     *
     * @param array $row 保存対象のデータ（連想配列）
     * @param string $table_name テーブル名
     * @param array|null $pk_names 主キーの列名（UPDATEのため）
     * @param array|null $ignore_names 保存対象外の列名
     * @return void
     * @throws \Exception 主キー指定なしで重複レコードが存在する場合
     */
    public function save(array $row, string $table_name, ?array $pk_names = null, ?array $ignore_names = null) {
        if (empty($row)) { return; }
        $table_name = " " . $table_name . " ";
        if ($pk_names === null) { $pk_names = []; }
        if ($ignore_names === null) { $ignore_names = []; }

        $where = null;
        $where_prms = [];
        if (!empty($pk_names)) {
            foreach ($pk_names as $f) {
                $where = $where === null ? " WHERE " : $where . "AND ";
                $where .= "`" . $f . "`=:" . $f . " ";
                $where_prms[":" . $f] = $row[$f];
            }
        }

        $cnt = 0;
        if ($where !== null) {
            $cnt = $this->count("SELECT 0 FROM" . $table_name . $where, $where_prms);
        }

        if ($cnt > 0 && $where === null) {
            throw new \Exception("Primary key required for update.");
        }

        $fs = [];
        $vs = [];
        $prms = [];

        if ($cnt === 0) {
            foreach ($row as $k => $v) {
                if (in_array($k, $pk_names) || in_array($k, $ignore_names)) { continue; }
                $fs[] = "`" . $k . "`";
                $vs[] = ":" . $k;
                $prms[":" . $k] = $v;
            }
            $sql = "INSERT INTO " . $table_name . "(" . implode(",", $fs) . ") VALUES(" . implode(",", $vs) . ")";
            $this->exec($sql, $prms);
        } else {
            foreach ($row as $k => $v) {
                if (in_array($k, $pk_names) || in_array($k, $ignore_names)) { continue; }
                $fs[] = "`" . $k . "`=:" . $k;
                $prms[":" . $k] = $v;
            }
            $sql = "UPDATE" . $table_name . "SET " . implode(",", $fs) . $where;
            $this->exec($sql, $prms + $where_prms);
        }
    }

    /**
     * オブジェクト（エンティティ）をテーブルに保存する
     *
     * @param object $entity 保存するオブジェクト
     * @param string $table_name テーブル名
     * @param array|null $pk_names 主キーの列名
     * @param array|null $ignore_names 保存対象外の列名
     * @return void
     */
    public function saveEntity($entity, string $table_name, ?array $pk_names = null, ?array $ignore_names = null) {
        if (!isset(self::$C_FLD[$entity::class])) {
            $flds = [];
            $ref = new \ReflectionClass($entity::class);
            $props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($props as $p) {
                if ($p->isPublic()) {
                    $flds[] = $p->getName();
                }
            }
            self::$C_FLD[$entity::class] = $flds;
        } else {
            $flds = self::$C_FLD[$entity::class];
        }

        $row = [];
        foreach ($flds as $f) {
            $row[$f] = $entity->$f;
        }

        return $this->save($row, $table_name, $pk_names, $ignore_names);
    }

    /**
     * トランザクション内で処理を実行する
     *
     * @param callable $callback コールバックにはこのインスタンスが渡される
     * @return void
     * @throws \Throwable コールバック内の例外はロールバック後に再スローされる
     */
    public function transaction(callable $callback): void {
        try {
            $this->beginTransaction();
            $callback($this);
            $this->commit();
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }
}

/**
 * 軽量Action分岐ライブラリ
 * 処理条件に応じたハンドラを定義・実行できるクラス
 */
class ActionHandler {
    protected array $handlers = [];
    protected $default = null;
    protected $errorHandler = null;
    protected $before = null;
    protected $after = null;
    protected $finally = null;

    public static function getInstance(?callable $default_callback = null): static {
        $ins = new static();
        if ($default_callback !== null) {
            $ins->default($default_callback);
        }
        return $ins;
    }
    public function default(callable $callback): static {
        $this->default = $callback;
        return $this;
    }
    public function when(callable $condition, callable $callback): static {
        if ($condition()) {
            $this->handlers[] = fn() => $callback();
        }
        return $this;
    }
    public function isGet(callable $callback): static {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->handlers[] = fn() => $callback();
        }
        return $this;
    }
    public function isPost(callable $callback): static {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlers[] = fn() => $callback();
        }
        return $this;
    }
    public function isAjax(callable $callback): static {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->handlers[] = fn() => $callback();
        }
        return $this;
    }
    public function has(string $key, callable $callback): static {
        if (isset($_POST[$key])) {
            $this->handlers[] = fn() => $callback($_POST[$key]);
        } elseif (isset($_GET[$key])) {
            $this->handlers[] = fn() => $callback($_GET[$key]);
        }
        return $this;
    }
    public function hasGet(string $key, callable $callback): static {
        if (isset($_GET[$key])) {
            $this->handlers[] = fn() => $callback($_GET[$key]);
        }
        return $this;
    }
    public function hasPost(string $key, callable $callback): static {
        if (isset($_POST[$key])) {
            $this->handlers[] = fn() => $callback($_POST[$key]);
        } 
        return $this;
    }
    public function match(string $key, $value, callable $callback): static {
        if (isset($_POST[$key]) && $_POST[$key] == $value) {
            $this->handlers[] = fn() => $callback($_POST[$key]);
        } elseif (isset($_GET[$key]) && $_GET[$key] == $value) {
            $this->handlers[] = fn() => $callback($_GET[$key]);
        }
        return $this;
    }
    public function matchGet(string $key, $value, callable $callback): static {
        if (isset($_GET[$key]) && $_GET[$key] == $value) {
            $this->handlers[] = fn() => $callback($_GET[$key]);
        }
        return $this;
    }
    public function matcPost(string $key, $value, callable $callback): static {
        if (isset($_POST[$key]) && $_POST[$key] == $value) {
            $this->handlers[] = fn() => $callback($_POST[$key]);
        }
        return $this;
    }

    public function onError(callable $callback): static {
        $this->errorHandler = $callback;
        return $this;
    }

    public function before(callable $callback): static {
        $this->before = $callback;
        return $this;
    }

    public function after(callable $callback): static {
        $this->after = $callback;
        return $this;
    }

    public function finally(callable $callback): static {
        $this->finally = $callback;
        return $this;
    }

    public function run(bool $ret_all = false): mixed {
        $results = [];

        try {
            if ($this->before) {
                $result = call_user_func($this->before);
                if ($result !== null) $results[] = $result;
            }

            if (!empty($this->handlers)) {
                foreach ($this->handlers as $handler) {
                    $result = call_user_func($handler);
                    if ($result !== null) $results[] = $result;
                }
            } elseif ($this->default) {
                $result = call_user_func($this->default);
                if ($result !== null) $results[] = $result;
            }

            if ($this->after) {
                $result = call_user_func($this->after);
                if ($result !== null) $results[] = $result;
            }

        } catch (\Throwable $e) {
            if ($this->errorHandler) {
                $result = call_user_func($this->errorHandler, $e);
                if ($result !== null) $results[] = $result;
            } else {
                throw $e;
            }
        } finally {
            if ($this->finally) {
                $result = call_user_func($this->finally);
                if ($result !== null) $results[] = $result;
            }
        }

        return $ret_all ? $results : (empty($results) ? null : end($results));
    }
}

class Storage{
    protected static array $temp = [];
    protected static array $flash = [];
    protected static bool $booted = false;
    protected static function boot(): void {
        if (static::$booted) return;
        static::$booted = true;

        if (!session_id()) session_start();

        if (isset($_SESSION['_flash'])) {
            static::$flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
        }
    }
    // Temp（一時データ）
    public static function put(string $key, mixed $value): void {
        static::boot();
        static::$temp[$key] = $value;
    }
    public static function get(string $key, mixed $default = null): mixed {
        static::boot();
        return static::$temp[$key] ?? $default;
    }
    public static function all(): array {
        static::boot();
        return static::$temp;
    }
    public static function clear(): void {
        static::boot();
        static::$temp = [];
    }
    // Flash（次リクエストだけ有効）
    public static function setFlash(string $key, mixed $value): void {
        static::boot();
        $_SESSION['_flash'][$key] = $value;
    }
    public static function getFlash(string $key, mixed $default = null): mixed {
        static::boot();
        return static::$flash[$key] ?? $default;
    }

    public static function hasFlash(string $key): bool {
        static::boot();
        return isset(static::$flash[$key]);
    }

    // Session（永続）
    public static function setSession(string $key, mixed $value): void {
        static::boot();
        $_SESSION[$key] = $value;
    }

    public static function getSession(string $key, mixed $default = null): mixed {
        static::boot();
        return $_SESSION[$key] ?? $default;
    }

    public static function forgetSession(string $key): void {
        static::boot();
        unset($_SESSION[$key]);
    }
}

class Form {
    protected array $data = [];
    protected array $validators = [];
    protected array $errors = [];
    protected ?string $csrfToken = null;

    public static function formRequest():self{
        return new self();
    }

    public function __construct(?array $source = null) {
        $this->data = $source ?? array_merge($_GET, $_POST);
    }

    public function keyList(): array {
        return array_keys($this->data);
    }

    public function all(): array {
        return $this->data;
    }

    public function set(string $key, $value): void {
        $this->data[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool {
        return isset($this->data[$key]);
    }

    public function isEmpty(?string $key = null): bool {
        if ($key === null) {
            return empty($this->data);
        }
        if (! $this->has($key)) {
            return false;
        }
        return empty($this->data[$key]);
    }

    public function rend(string $key): FormRender{
        return new FormRender($key, $this);
    }

    public function setRules(callable $closure): void {
        $closure = $closure->bindTo($this, self::class);
        $closure();
    }

    public function rule(string $key, ?string $label = null): FormValidator {
        if (!isset($this->validators[$key])) {
            $this->validators[$key] = new FormValidator($key, $label ?? $key, $this);
        }
        return $this->validators[$key];
    }

    public function addError(string $key, string $message): void {
        $this->errors[$key] = $message;
    }

    public function setCsrfToken(string $token): void {
        $this->csrfToken = $token;
    }

    public function getCsrfToken(): ?string {
        return $this->csrfToken;
    }

    public function validate(): bool {
        $ok = true;
        $this->errors = [];
        foreach ($this->validators as $key => $validator) {
            $value = $this->get($key);
            if (! $validator->validate($value)) {
                $ok = false;
                $this->errors[$key] = $validator->getLastMessage();
            }
        }
        return $ok;
    }

    public function hasError(){
        return !empty($this->errors);
    }
    public function getErrors(): array {
        return $this->errors;
    }

    public function rendErrors(string $class = 'error', string $tag = 'div'): void {
        $errors = $this->getErrors();
        foreach($errors as $er){
            echo '<'.$tag.' class="' . h($class) . '">'
            . h($er)
            . '</'.$tag.'>';
        }
    }
}

class FormRender {
    protected string $key;
    protected Form $form;

    public function __construct(string $key, Form $form) {
        $this->key = $key;
        $this->form = $form;
    }


    public function name(): self {
        echo $this->getName();
        return $this;
    }

    public function value($default = null): self {
        echo $this->getValue($default);
        return $this;
    }

    public function checked($value = 'on'): self {
        $current = $this->form->get($this->key);
        if ($current == $value) {
            echo 'checked';
        }
        return $this;
    }

    public function selected($value): self {
        $current = $this->form->get($this->key);
        if ($current == $value) {
            echo 'selected';
        }
        return $this;
    }

    public function options(array $list, $use_keys = false): void {
        $current = $this->form->get($this->key);
        foreach ($list as $k => $v) {
            $value = $use_keys ? $k : $v;
            $label = $v;
            $selected = ($current == $value) ? ' selected' : '';
            echo '<option value="' . h((string)$value) . '"' . $selected . '>'
                . h((string)$label)
                . '</option>';
        }
    }

    public function getName(): string {
        return 'name="' . h($this->key) . '"';
    }

    public function getValue($default = null): string {
        $val = $this->form->get($this->key, $default);
        return 'value="' . h($val) . '"';
    }

    // 汎用属性
    public function attr(string $name, $value): self {
        echo $name . '="' . h((string)$value) . '"';
        return $this;
    }

}

class FormValidator {
    protected static string $ini_file_default = __DIR__.DIRECTORY_SEPARATOR."validator-lang.ini";
    protected static array $ini_messages = [];
    protected static bool $ini_loaded = false;

    protected string $field;
    protected string $label;
    protected Form $form;
    protected array $rules = [];
    protected ?string $lastMessage = null;

    public static function setLang(string $ini_file): void {
        if (file_exists($ini_file)) {
            self::$ini_messages = parse_ini_file($ini_file);
            self::$ini_loaded = true;
        }
    }
    private static function _lang_msg(string $key, array $replacements = []): string {
        if(self::$ini_loaded === false){
            self::setLang(self::$ini_file_default);
        }

        $template = self::$ini_messages[$key] ?? $key;
        foreach ($replacements as $k => $v) {
            $template = str_replace(":$k", (string)$v, $template);
        }
        return $template;
    }

    public function __construct(string $field, string $label, Form $form) {
        $this->field = $field;
        $this->label = $label;
        $this->form = $form;
    }

    public function custom(callable $rule, string $message_key = 'custom_error', array $args = []): static {
        return $this->addRule(
            $rule,
            $message_key,
            array_merge(['field' => $this->label], $args)
        );
    }
    
    protected function addRule(callable $rule, string $message_key, array $message_args): static {
        $this->rules[] = [
            'rule' => $rule,
            'message' => self::_lang_msg($message_key, $message_args),
        ];
        return $this;
    }

    public function required(): static {
        return $this->addRule(
            function($v){
                return $v !== null && $v !== '';
            },
            'required', [
                'field' => $this->label
            ],
        );
    }
    public function match(string $otherField): static {
        return $this->addRule(
            fn($v) => $v === $this->form->get($otherField),
            'match',
            ['field' => $this->label, 'other' => $otherField]
        );
    }
    public function regex(string $pattern): static {
        return $this->addRule(
            fn($v) => preg_match($pattern, $v),
            'regex',
            ['field' => $this->label]
        );
    }
    public function inArray(array $options): static {
        return $this->addRule(
            fn($v) => in_array($v, $options, true),
            'inArray',
            ['field' => $this->label]
        );
    }
    public function minLength(int $len): static {
        return $this->addRule(
            fn($v) => is_string($v) && mb_strlen($v) >= $len,
            'minLength', [
                'field' => $this->label,
                'len' => $len
            ]
        );
    }

    public function maxLength(int $len): static {
        return $this->addRule(
            fn($v) => is_string($v) && mb_strlen($v) <= $len,
            'maxLength',
            ['field' => $this->label, 'len' => $len]
        );
    }

    public function email(): static {
        return $this->addRule(
            function($v) {
                return filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
            },
            'email', [
                'field' => $this->label,
            ]
        );
    }

    public function csrfToken(string $expected): static {
        return $this->addRule(
            function($v) use ($expected){
                return $v === $expected;
            },
            'csrfToken', [
                'field' => $this->label,
            ]
        );
    }

    public function validate($value): bool {
        foreach ($this->rules as $item) {
            if (!($item['rule'])($value)) {
                $this->lastMessage = $item['message'];
                return false;
            }
        }
        return true;
    }

    public function getLastMessage(): ?string {
        return $this->lastMessage;
    }
}