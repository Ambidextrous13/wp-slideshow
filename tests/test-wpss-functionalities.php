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
        // echo '5';
        $ids = $this->wpss_class_save_the_images();
        $this->assertTrue( is_array( $ids ) && ! empty( $ids ) );
    }

}
?>