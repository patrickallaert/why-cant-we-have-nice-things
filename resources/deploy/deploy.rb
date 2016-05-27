# config valid only for current version of Capistrano
lock '3.5.0'

set :application, 'why-cant-we-have-nice-things'
set :ssh_options, {forward_agent: true}

# Repository
set :scm, :git
set :repo_url, 'git@github.com:madewithlove/why-cant-we-have-nice-things.git'
set :branch, 'master'

# Logging
set :log_level, :debug
Airbrussh.configure do |config|
    config.command_output = true
    config.log_file = "storage/logs/capistrano.log"
end

# Plugins
set :composer_install_flags, '--prefer-dist --no-interaction --optimize-autoloader'

# Default value for :linked_files is []
set :linked_files, fetch(:linked_files, []).push('.env')

# Tasks
namespace :deploy do
    before :updated, "npm:install"
    before :updated, "application:assets"
    before :updated, "database:migrate"
    before :updated, "application:refresh"
    before :finishing, "cache:clear"
end
