namespace :cache do
    desc "Clear FPM cache"
    task :clear do
        on roles(:all) do
            within release_path do
                execute :php, "vendor/bin/cachetool opcache:reset --fcgi=/var/run/php/php7.0-fpm.sock"
            end
        end
    end
end
