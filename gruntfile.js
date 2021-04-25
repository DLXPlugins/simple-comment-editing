module.exports = function (grunt) {
	grunt.initConfig({
		compress: {
			main: {
			  options: {
				archive: 'simple-comment-editing.zip'
			  },
			  files: [
				{src: ['simple-comment-editing.php'], dest: '/', filter: 'isFile'}, // includes files in path
				{src: ['index.php'], dest: '/', filter: 'isFile'}, // includes files in path
				{src: ['readme.txt'], dest: '/', filter: 'isFile'}, // includes files in path
				{src: ['uninstall.php'], dest: '/', filter: 'isFile'}, // includes files in path
				{src: ['images/**'], dest: '/'}, // includes files in path and its subdirs
				{src: ['includes/**'], dest: '/'}, // includes files in path and its subdirs
				{src: ['js/**'], dest: '/'}, // includes files in path and its subdirs
				{src: ['languages/**'], dest: '/'}, // includes files in path and its subdirs
			  ]
			}
		  }
	  });
	  grunt.registerTask('default', ["compress"]);

 
 
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
   
 };
