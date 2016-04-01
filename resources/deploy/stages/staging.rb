set :stage, :staging

# Server configuration
# ======================
server 'why-cant-we-have-nice-things.mwl.be', user: ENV['SSH_USER'], roles: %w{web}

# Stage settings
# ======================
set :branch, 'feature/auto-deploy'
set :deploy_to, '/home/forge/dev.why-cant-we-have-nice-things.mwl.be'
