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
}

?>