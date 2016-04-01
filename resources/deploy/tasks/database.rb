namespace :database do
    desc "Run the migrations"
    task :migrate do
        on roles(:all) do
            within release_path do
                execute :npm, "run migrate"
            end
        end
    end
end
