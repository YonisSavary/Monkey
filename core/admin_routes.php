<?php

use Monkey\Config;
use Monkey\Router;

// CONFIGURATION USAGE
$admin_url = "/admin";
if (Config::exists("admin_url_prefix")) $admin_url = Config::get("admin_url_prefix");

$api_password = Config::get("admin_api_password");

// ADMIN ROUTE DEFINITION

Router::add_temp("$admin_url", "m_admin->redirect");
Router::add_temp("$admin_url/index", "m_admin->index", "m_index");
Router::add_temp("$admin_url/api/index/create"    , "m_api_index->create"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/index/read"    , "m_api_index->read"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/index/update"  , "m_api_index->update" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/index/delete"  , "m_api_index->delete" , null, ["m_middle_password"]);

Router::add_temp("$admin_url/configuration"             , "m_admin->configuration", "m_configuration");
Router::add_temp("$admin_url/api/configuration/create"    , "m_api_configuration->create"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/configuration/read"    , "m_api_configuration->read"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/configuration/update"  , "m_api_configuration->update" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/configuration/delete"  , "m_api_configuration->delete" , null, ["m_middle_password"]);

Router::add_temp("$admin_url/documentation"             , "m_admin->documentation", "m_documentation");/*
Router::add_temp("$admin_url/api/documentation/create"  , "m_api_documentation->create" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/documentation/read"    , "m_api_documentation->read"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/documentation/update"  , "m_api_documentation->update" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/documentation/delete"  , "m_api_documentation->delete" , null, ["m_middle_password"]);
*/
Router::add_temp("$admin_url/model"             , "m_admin->model", "m_model");
Router::add_temp("$admin_url/api/model/create"  , "m_api_model->create" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/model/read"    , "m_api_model->read"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/model/update"  , "m_api_model->update" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/model/delete"  , "m_api_model->delete" , null, ["m_middle_password"]);

Router::add_temp("$admin_url/route"             , "m_admin->route", "m_route");
Router::add_temp("$admin_url/api/route/create"  , "m_api_route->create" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/route/read"    , "m_api_route->read"   , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/route/update"  , "m_api_route->update" , null, ["m_middle_password"]);
Router::add_temp("$admin_url/api/route/delete"  , "m_api_route->delete" , null, ["m_middle_password"]);

Router::add_temp("$admin_url/api/route/import"  , "m_api_route->import" , "m_api_route_import", ["m_middle_password"]);
Router::add_temp("$admin_url/api/route/export"  , "m_api_route->export" , "m_api_route_export", ["m_middle_password"]);