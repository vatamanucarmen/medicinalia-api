set :application, "medicinalia"
set :repository,  "https://github.com/vatamanucarmen/medicinalia-api.git"
ssh_options[:port] = "4200"

# set :scm, :git # You can set :scm explicitly or Capistrano will make an intelligent guess based on known version control directory names
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `git`, `mercurial`, `perforce`, `subversion` or `none`

role :web, "medicinalia.geton.ro"                          # Your HTTP server, Apache/etc
role :app, "medicinalia.geton.ro"                          # This may be the same as your `Web` server
role :db,  "medicinalia.geton.ro", :primary => true

set :user,        "root"
set :deploy_to,   "/var/www/medicinalia"
set :deploy_via, :remote_cache
set :normalize_asset_timestamps, false

## Git info
set :branch, "master"
set :scm, :git


set :dump_assetic_assets, true
set :use_sudo, false
set :interactive_mode, true

## Normal tasks
set :use_composer, true
set :composer_options, "--no-dev --verbose --prefer-dist --optimize-autoloader"
set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     ["app/logs", "web/uploads", "vendor"]
set :writable_dirs,     ["app/cache", "app/logs", "web/uploads"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true

set  :keep_releases,  3

after "symfony:composer:install", "deploy:cleanup"

# Be more verbose by uncommenting the following line
logger.level = Logger::DEBUG

task :upload_parameters do
  origin_file = "app/config/parameters_deploy.yml"
  destination_file = shared_path + "/app/config/parameters.yml"
  try_sudo "mkdir -p #{File.dirname(destination_file)}"
  top.upload(origin_file, destination_file)
end

before "symfony:composer:install", "upload_parameters"
