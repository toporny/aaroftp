
module.exports = function (grunt) {

  grunt.initConfig({
    env: grunt.option('env') || 'dev',  	
    clean: {
      archive: {
        src: [ '_build' ]
      }
    },  	
  	compress: {
	  main: {
		options: {
		    archive: '_build/archive.zip'
		},

	    src: [
	    	'*',
			'!_build/**',
			'!.env',
			'!node_modules/**',
			'!vendor/**',
			'!home_unpack.php',
			'app/**',
			'bootstrap/**',
			'config/**',
			'database/**',
			'!public/.htaccess',
			'!products_local_storage',
			'public/**',
			'resources/**',
			'storage/**',
			'tests/**'
        ],
	    dest: '',
	  }
	},
	ftp_push: {
		your_target: {
		  options: {
		    authKey: 'server_'+'<%= env %>',
		    host: "alltic.home.pl",
		    dest: ".",
		    port: 21
		  },
		  files: [
		    {
		      expand: true,
		      cwd: '.',
		      src: [
		        "_build/archive.zip",
		      ], dest: ''
		    },
		    {
		      expand: true,
		      cwd: '.',
		      src: [
		        "home_unpack.php",
		      ], dest: 'public/'
		    }		  ]
		}
	},
	http: {
		repack_everything: {
		  options: {
		    url: 'http://alltic.home.pl/aaron/aaronftp_<%= env %>/public/home_unpack.php',
		  },
		  dest: 'tmp.repack.log'
		}
	}	

})

grunt.loadNpmTasks('grunt-contrib-clean');
grunt.loadNpmTasks('grunt-ftp-push');
grunt.loadNpmTasks('grunt-shell');
grunt.loadNpmTasks('grunt-contrib-compress');
grunt.loadNpmTasks('grunt-http');

grunt.registerTask('default',
	[
	 'compress',
	 'ftp_push',
	 'http'
	]);


};

