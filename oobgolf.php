<?php
/*
Plugin Name: oobgolf Widgets
Plugin URI: http://timlaqua.com/
Description: Collection of widgets to display oobgolf.com information on your blog
Version: 1.0.2
Author: Tim Laqua
Author URI: http://timlaqua.com
*/

/*  Copyright 2009  Tim Laqua  (email : t.laqua@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('oobgolf.class.php');

add_action('plugins_loaded',array('widget_oobgolf','register'));
add_action('wp_head',array('widget_oobgolf','addHeaderCode'));
add_action('admin_head',array('widget_oobgolf','addAdminHeaderCode'));
register_activation_hook( __FILE__, array('widget_oobgolf','activate'));
register_deactivation_hook( __FILE__, array('widget_oobgolf','deactivate'));
