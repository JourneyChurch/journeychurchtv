module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    watch: {
      css: {
        files: ['scss/*.scss'],
        tasks: ['sass']
      },
      js: {
        files: ['ts/*.ts'],
        tasks: ['typescript']
      }
    },
    sass: {
      dist: {
        files: [{
          expand: true,
          cwd: 'scss/',
          src: ['*.scss'],
          dest: 'css/',
          ext: '.css'
        }]
      }
    },
    typescript: {
      base: {
        src: ['ts/*.ts'],
        dest: 'js'
      }
    },
  });

  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-typescript');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task(s).
  grunt.registerTask('default', ['watch']);

};
