module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    watch: {
      files: ['scss/*.scss'],
      tasks: ['sass']
    },
    sass: {
      dist: {
        files: {
          'css/main.css': 'scss/main.scss'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-sass');
  //grunt.loadNpmTasks('grunt-typescript');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task(s).
  grunt.registerTask('default', ['watch']);

};
