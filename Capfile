set :deploy_config_path, 'resources/deploy/deploy.rb'
set :stage_config_path, 'resources/deploy/stages'

# Load Dotenv
require 'dotenv'
Dotenv.load()

# Load DSL and set up stages
require 'capistrano/setup'

# Include default deployment tasks
require 'capistrano/deploy'

# Third party tasks
require 'capistrano/composer'
require 'airbrussh/capistrano'

# Custom tasks
Dir.glob('resources/deploy/tasks/*.rb').each { |r| import r }
