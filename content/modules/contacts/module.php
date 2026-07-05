<?php

use RuEdu\Engine\Hook;
use RuEdu\Engine\Template;
use RuEdu\Engine\SEO;
use RuEdu\Engine\Config;
use RuEdu\Engine\Auth;
use RuEdu\Engine\Session;
use RuEdu\Engine\Router;
use RuEdu\Engine\Database;
use RuEdu\Model\Menu;
use RuEdu\Model\Setting;

Hook::on('register_routes', function ($router) {
    $router->get('/contacts', function () {
        $template = new Template();
        echo $template->setData([
            'menu' => Menu::getByLocation('main'),
            'phone' => Setting::get('contact_phone', Config::get('contact_phone', '')),
            'address' => Setting::get('contact_address', Config::get('contact_address', '')),
            'email' => Config::get('admin_email', ''),
            'yandex_map' => Setting::get('yandex_map', ''),
            'meta' => SEO::metaTags(['title' => 'Контакты — ' . Config::get('site_name')]),
        ])->render('contacts');
    });
});

Hook::on('admin_menu', function ($menu) {
    $menu[] = ['title' => 'Заявки', 'url' => Router::path('admin/forms/submissions'), 'icon' => 'bi-envelope'];
    return $menu;
});
