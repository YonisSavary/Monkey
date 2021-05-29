<?php

use Monkey\Framework\AppLoader;
use PHPUnit\Framework\TestCase;

class AppLoaderTest extends TestCase
{
    public function test_get_app_directories()
    {
        $app_dir_test = ["TestApplication"];
        AppLoader::$app_directories = $app_dir_test;
        $this->assertEquals(AppLoader::get_app_directories(), $app_dir_test);
    }

    public function test_get_config_paths()
    {

    }

    public function test_get_views_directories()
    {

    }

    public function test_get_autoload_list()
    {

    }

    public function test_explore_full_dir()
    {

    }

    public function test_load_application_path()
    {

    }

    public function test_load_all_applications()
    {

    }

    public function test_write_to_register()
    {

    }

    public function test_read_from_register()
    {

    }

    public function test_init()
    {

    }

}