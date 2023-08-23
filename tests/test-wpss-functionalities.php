<?php

Use WPSS\Inc\Classes\Wpss;
use PHPUnit\Framework\TestCase;

class WpssTest extends TestCase {

    protected $wpss;

    protected $attachment_count;

    public function __construct(){
        parent::__construct();
    }

    protected function setUp(): void {
        parent::setUp();
        require_once dirname( __FILE__, 2 ) . '/wp-slideshow.php';
        $this->wpss = new Wpss();
    }

    public function test_constructor() {
		$this->assertInstanceOf( Wpss::class, $this->wpss );
    }
	
	public function test_wpss_class_init_hooks() {
		$this->wpss->init_hooks();
		$this->assertTrue( 0 < has_action( 'admin_menu', [ $this->wpss, 'menu_registrar' ] ) );
	}
    
    public function test_wpss_class_menu_registrar() {
		global $menu;
		$this->wpss->menu_registrar();

        $menu_slug_exists = false;
        foreach ($menu as $menu_item) {
            if ($menu_item[2] === 'wpss-plugin') {
                $menu_slug_exists = true;
                break;
            }
        }
        $this->assertTrue($menu_slug_exists, 'Menu page should be added.');
	}

	protected function wpss_class_save_the_images() {
        $img_1 = dirname( __FILE__ ) . '/assets/pexels-rania-alhamed-2454533.jpg';
        $img_2 = dirname( __FILE__ ) . '/assets/pexels-shantanu-pal-2679501.jpg';

        $tmp_1 = wp_tempnam( $img_1 );
        $tmp_2 = wp_tempnam( $img_2 );
        $_FILES = [
            'files' => [
                'name'      => ['pexels-rania-alhamed-2454533.jpg', 'pexels-shantanu-pal-2679501.jpg'],
                'type'      => ['image/jpeg', 'image/jpeg'],
                'full_path' => ['pexels-rania-alhamed-2454533.jpg', 'pexels-shantanu-pal-2679501.jpg'],
                'tmp_name'  => [ $tmp_1, $tmp_2 ],
                'error'     => [0, 0],
                'size'      => [ filesize( $img_1 ) ,  filesize( $img_2 ) ],
            ]
        ];

        copy( $img_1, $tmp_1 );
        copy( $img_2, $tmp_2 );

        $this->attachment_count = 2;
        return $this->wpss->save_the_images( 'test_iptc_upload' );
    }
	
    public function test_wpss_class_save_the_images() {
        $ids = $this->wpss_class_save_the_images();
        $this->assertTrue( is_array( $ids ) && ! empty( $ids ) );
    }

	public function test_wpss_class_wpss_enqueue_images() {
        $ids = $this->wpss_class_save_the_images();
        $this->assertTrue( $this->wpss->wpss_enqueue_images( $ids ) );
    }

    public function test_wpss_class_db_inserter() {
        $data = [
            'slide_start' => 1,
            'slide_end'   => 2 * $this->attachment_count,
            'slide_limit' => 1,
            'prev_height' => 200,
            'prev_width'  => 220,
            'prev_is_sq'  => 0,
            'web_height'  => 500,
            'web_width'   => 540,
            'web_is_sq'   => 0,
        ];

        $this->assertTrue( $this->wpss->db_inserter( null, $data ) );
    }

    public function test_wpss_class_db_slides_fetcher() {
        $data = $this->wpss->db_slides_fetcher( true );
        $this->assertTrue( is_array( $data ) && ! empty( $data ) );

        $data = $this->wpss->db_slides_fetcher( false );
        $this->assertTrue( is_array( $data ) && ! empty( $data ) );
    }

	protected function ajax_caller( $action ) {
        ini_set( 'implicit_flush', false );

        ob_start();
        do_action($action);
        $response = ob_get_clean();

        $return_arr = [
            'success' => false,
            'message' => 'Invalid JSON response',
        ];

        $pattern = '/\{.*?\}/';
        if (preg_match($pattern, $response, $matches)) {
            $extractedString = $matches[0];
            if (json_decode($extractedString, true)) {
                $return_arr = json_decode($extractedString, true);
            }
        }
        ini_set('implicit_flush', true);
        
        return $return_arr;
    }
    
    public function test_wpss_class_fetch_settings() {
        $nonce  = wp_create_nonce( 'pointBreak' );
        $action = 'wpss_plugin_settings_fetcher';

        $_POST['ajaxNonce']    = $nonce;
        $_REQUEST['ajaxNonce'] = $nonce;
        $_POST['action']       = $action;
        $_REQUEST['action']    = $action; 

        $response = $this->ajax_caller( 'wp_ajax_wpss_plugin_settings_fetcher' );
        $assert_arg = true;
        
        foreach ( $this->wpss::TABLE_KEYS as $key ) {
            $assert_arg = $assert_arg && isset( $response[ $key ] );
        }

        $this->assertTrue( $assert_arg );
    }

    public function test_wpss_class_settings_saver() {
        $nonce  = wp_create_nonce( 'pointBreak' );
        $action = 'wpss_plugin_settings_setter';
        
        $_POST['ajaxNonce']    = $nonce;
        $_REQUEST['ajaxNonce'] = $nonce;
        $_POST['action']       = $action;
        $_REQUEST['action']    = $action; 
        
        $_POST['wpss_settings'] = [
            'slide_start' => '1',
            'slide_end'   => '1',
            'slide_limit' => '1',
            'prev_height' => '100',
            'prev_width'  => '100',
            'prev_is_sq'  => '1',
            'web_height'  => '100',
            'web_width'   => '100',
            'web_is_sq'   => '1',
        ];

        $response = $this->ajax_caller( 'wp_ajax_wpss_plugin_settings_setter' );

        $this->assertTrue( isset( $response['alert_string'] ) && 'Saved!' === $response['alert_string'] );
                
        $assert_arg = true;
        $new_data   = $this->wpss->db_slides_fetcher( true );

        foreach ( $_POST['wpss_settings'] as $key => $value ) {
            $assert_arg = $assert_arg && $new_data[ $key ] === $value;
        }

        $this->assertTrue( $assert_arg );
    }

}
?>