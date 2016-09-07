# Directory paths
# -----------------------------------------------------------------------------
css_dir = "css"
sass_dir = "sass"


# Precision
# -----------------------------------------------------------------------------
Sass::Script::Number.precision = 5


# Environment
# -----------------------------------------------------------------------------
#environment = :development
environment = :production


# Output Style
# -----------------------------------------------------------------------------
# You can select your preferred output style here (:expanded, :nested, :compact
# or :compressed). :expanded is closest to Drupal coding standards and it is
# not necessary to compress in the preprocessor since Drupal will do this for
# us using its own aggregation and compression systems.
output_style = :expanded


# Assets
# -----------------------------------------------------------------------------
relative_assets = true


# Line Comments
# -----------------------------------------------------------------------------
line_comments = (environment == :development) ? true : false


# Sourcemaps
# -----------------------------------------------------------------------------
sourcemap = (environment == :development) ? true : false
