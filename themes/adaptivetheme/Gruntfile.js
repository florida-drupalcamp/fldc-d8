module.exports = function(grunt) {
  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    uglify: {
      min: {
        options: {
          sourceMap: true,
        },
        files: [{
          expand: true,
          mangle: false,
          preserveComments: 'some',
          src: '*.js',
          dest: 'at_core/scripts/min',
          cwd: 'at_core/scripts',
          rename: function(dest, src) { return dest + '/' + src.replace('.js', '.min.js'); }
        }]
      }
    },

    postcss: {
      atcore: {
        src: 'at_core/styles/css/*.css',
         options: {
          map: {
            inline: false
          },
          processors: [
            require('autoprefixer')({browsers: 'last 5 versions'})
          ]
        }
      },
      layout_plugin: {
        src: 'at_core/layout_plugin/css/**/*.css',
        options: {
          map: {
            inline: false
          },
          processors: [
            require('autoprefixer')({browsers: 'last 5 versions'})
          ]
        }
      },
      mimic: {
        src: 'at_core/ckeditor/skins/mimic/*.css',
        options: {
          map: {
            inline: false
          },
          processors: [
            require('autoprefixer')({browsers: 'last 7 versions'})
          ]
        }
      }
    },

    compass: {
      atcore: {
        options: {
          config: 'at_core/styles/config.rb',
          basePath: 'at_core/styles',
          bundleExec: true
        }
      },
      layout_plugin: {
        options: {
          config: 'at_core/layout_plugin/config.rb',
          basePath: 'at_core/layout_plugin',
          bundleExec: true
        }
      },
      mimic: {
        options: {
          config: 'at_core/ckeditor/skins/mimic/config.rb',
          basePath: 'at_core/ckeditor/skins/mimic',
          bundleExec: true
        }
      }
    },

    csslint: {
      atcore: {
        options: {
          csslintrc: '.csslintrc'
        },
        src: ['at_core/styles/css/*.css']
      },
      layout_plugin: {
        options: {
          csslintrc: '.csslintrc'
        },
        src: ['at_core/layout_plugin/css/**/*.css']
      },
      mimic: {
        options: {
          csslintrc: '.csslintrc'
        },
        src: ['at_core/ckeditor/skins/mimic/*.css']
      }
    },

    watch: {
      atcore: {
        files: 'at_core/styles/sass/*.scss',
        tasks: ['compass:atcore', 'postcss:atcore']
      },
      layout_plugin: {
        files: 'at_core/layout_plugin/sass/**/*.scss',
        tasks: ['compass:layout_plugin', 'postcss:layout_plugin']
      },
      mimic: {
        files: 'at_core/ckeditor/skins/mimic/sass/*.scss',
        tasks: ['compass:mimic', 'postcss:mimic']
      }
    }
  });

  grunt.loadNpmTasks('grunt-postcss');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-csslint');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', ['watch:atcore']);
}
