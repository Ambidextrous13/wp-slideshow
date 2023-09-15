<?php
/**
 * @group ajax
 */

Use WPSS\Inc\Classes\Wpss;
use PHPUnit\Framework\TestCase;

class WpssTest extends TestCase {

    protected $wpss;

    protected $attachment_count;

    public function __construct(){
        parent::__construct();
        
        add_filter('wp_die_handler', [ $this, 'immortal_bhavah' ] );
        add_filter('wp_die_ajax_handler', [ $this, 'immortal_bhavah' ] );
    }

    protected function setUp(): void {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        global $wpdb;
        require_once dirname( __FILE__, 2 ) . '/wp-slideshow.php';
        $this->wpss = new Wpss();
    }

    public function immortal_bhavah() {
        return [ $this, 'immortality_boon' ];
    }
    
    public function immortality_boon(){
        echo '';
    }

    public function test_create_the_wpss_plugin_data_table() {
        require_once dirname( __FILE__, 2 ) . '/inc/functions.php';

        global $wpdb;

        $table_name = $wpdb->prefix . 'wpss';
        $wpdb->query( ( 'DROP TABLE IF EXISTS ' . $table_name ) );

        $this->assertTrue( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name );

        create_the_wpss_plugin_data_table();

        $result = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
        $this->assertEquals( $table_name, $result );
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

    public function test_key_value_verifier_valid_data() {
        $valid_data = [
            'slide_order' => [1, 2, 3],
            'slide_start' => 1,
            'slide_end' => 3,
            'alignment' => 0,
            // Add other valid data here
        ];

        $result = $this->wpss->key_value_verifier($valid_data);
        $this->assertTrue($result);

        $invalid_data = [
            'slide_order' => [ 99, 163, 22 ],
            'slide_start' => 55,
            'slide_end'   => 22,
            'alignment'   => 4,
            'slide_limit' => 0,
            'prev_height' => -200,
            'prev_width'  => 145.33,
            'prev_is_sq'  => true,
            'prev_h_max'  => 1234,
            'prev_w_max'  => 4321,
            'web_height'  => 0,
            'web_width'   => 0,
            'web_is_sq'   => 33,
            'web_h_max'   => -11,
            'web_w_max'   => -18,
        ];

        $result = $this->wpss->key_value_verifier($invalid_data);
        $this->assertFalse($result);

        $db_data = $this->wpss->db_slides_fetcher( true );        
        $assert_arg = false;
        foreach ( $invalid_data as $key => $value ) {
            $assert_arg = $assert_arg || $db_data[ $key ] === $value;
        }

        $this->assertFalse( $assert_arg );
    }

    public function test_wpss_class_slides_rearrange() {
        $nonce  = wp_create_nonce( 'pointBreak' );

        $_POST['ajaxNonce']    = $nonce;
        $_REQUEST['ajaxNonce'] = $nonce;

        $db_data = $this->wpss->db_slides_fetcher();
        
        $first_slide  = $db_data['slide_order'][0];
        $second_slide = $db_data['slide_order'][1];

        $db_data['slide_order'][0] = $second_slide;
        $db_data['slide_order'][1] = $first_slide;
        $db_data['slide_order'][]  = '-1';

        $_POST['slideOrder'] = $db_data['slide_order'];
        $response = $this->ajax_caller( 'wp_ajax_wpss_plugin_slide_rearrange' );
        $this->assertTrue( $response[ 'succeed' ] );
    }

    public function test_wpss_class_wpss_garbage_collector() {
        $db_data     = $this->wpss->db_slides_fetcher();
        $table_array = $db_data['slide_order'];
        $arr_len     = count( $table_array );
        $deleted_id  = $table_array[ $arr_len - 1 ];
        
        $new_array = $table_array;
        unset( $new_array[ $arr_len - 1 ] );

        $this->assertTrue( false !== wp_get_attachment_url( $deleted_id ) );
        $this->wpss->wpss_garbage_collector( $new_array, $table_array );
        $this->assertFalse( wp_get_attachment_url( $deleted_id ) );
    }

    public function test_wpss_class_frontend_hero() {
        $expectedHtml = '<div id="wpss-slideshow"';
        $result = $this->wpss->frontend_hero();
        $this->assertStringContainsString($expectedHtml, $result);
    }

    public function test_wpss_class_ajax_response() {
        $alert_string = 'Testing case 1';
        $data = [];
        $result = $this->wpss->ajax_response( $alert_string, false, $data );

        $this->assertTrue( 
            is_array( $result )
            && ! empty( $result ) 
            && isset( $result['alert_string'] ) 
            && $alert_string === $result['alert_string']
            && ! $result['succeed']
        );

        $alert_string = 'Testing case 2';
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];
        $result = $this->wpss->ajax_response( $alert_string, true, $data );
        $this->assertTrue( 
            is_array( $result )
            && ! empty( $result ) 
            && isset( $result['alert_string'] ) 
            && $alert_string === $result['alert_string']
            && isset( $result['key1'] )
            && 'value1' === $result['key1']
            && isset( $result['key2'] )
            && 'value2' === $result['key2']
            && isset( $result['key3'] )
            && 'value3' === $result['key3']
            && $result['succeed']
        );

    }
}

?>