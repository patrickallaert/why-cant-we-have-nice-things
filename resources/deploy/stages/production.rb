set :stage, :production

# Server configuration
# ======================
server 'why-cant-we-have-nice-things.mwl.be', user: ENV['SSH_USER'], roles: %w{web}

# Stage settings
# ======================
set :branch, 'master'
set :deploy_to, '/home/forge/why-cant-we-have-nice-things.mwl.be'
