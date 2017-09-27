module.exports = function (grunt) {
    // Project configuration.
    grunt.initConfig({
        modx: grunt.file.readJSON('_build/config.json'),
        bump: {
            copyright: {
                files: [{
                    src: 'core/components/stupidquestion/model/stupidquestion/stupidquestion.class.php',
                    dest: 'core/components/stupidquestion/model/stupidquestion/stupidquestion.class.php'
                }],
                options: {
                    replacements: [{
                        pattern: /Copyright 2010(-\d{4})? by/g,
                        replacement: 'Copyright ' + (new Date().getFullYear() > 2010 ? '2010-' : '') + new Date().getFullYear() + ' by'
                    }]
                }
            },
            version: {
                files: [{
                    src: 'core/components/stupidquestion/model/stupidquestion/stupidquestion.class.php',
                    dest: 'core/components/stupidquestion/model/stupidquestion/stupidquestion.class.php'
                }],
                options: {
                    replacements: [{
                        pattern: /version = '\d+.\d+.\d+[-a-z0-9]*'/ig,
                        replacement: 'version = \'' + '<%= modx.version %>' + '\''
                    }]
                }
            },
            docs: {
                files: [{
                    src: 'mkdocs.yml',
                    dest: 'mkdocs.yml'
                }],
                options: {
                    replacements: [{
                        pattern: /&copy; \d{4}(-\d{4})?/g,
                        replacement: '&copy; ' + (new Date().getFullYear() > 2010 ? '2010-' : '') + new Date().getFullYear()
                    }]
                }
            }
        }
    });

    //load the packages
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.renameTask('string-replace', 'bump');

    //register the task
    grunt.registerTask('default', ['bump']);
};
