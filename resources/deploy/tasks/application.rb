namespace :application do
    desc "Builds the assets"
    task :assets do
        on roles(:all) do
            within release_path do
                execute :npm, 'rebuild node-sass optipng-bin'
                execute :npm, "run build:production -- --bail"
            end
        end
    end
end
