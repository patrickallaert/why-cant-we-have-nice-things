namespace :npm do
    desc "install dependencies"
    task :install do
        on roles(:all) do
            execute "cd #{release_path}; /usr/bin/npm-cache install npm --production --no-spin"
        end
    end
end
