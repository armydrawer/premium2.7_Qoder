/*
version: 0.7
*/

jQuery(function($) {
	
    var defaults = { 
		trigger: '.js_word_count',
	};
	
    $.fn.TextareaWordCount = function(method, params) {
        var options = $.extend({}, defaults, options, params);
        var now_obj = $(this);
 
		var trigger = $.trim(options['trigger']);
		
		function strip_content(s) {
			
			s = $.trim(s);
			s = s.replace(/(^\s*)|(\s*$)/gi,"");
			s = s.replace(/[ ]{2,}/gi," ");
			s = s.replace(/\n /,"\n");
			
			return s;
		}

		function check_editor_words(object) {
			
			var editor_content = object.val();
			editor_content = strip_content(editor_content);
			var cw = 0;
			var cs = editor_content.length;
			if (cs > 0) {
				cw = editor_content.split(' ').length; 
			}
			var to_words = object.attr('to-words');
			var to_symbols = object.attr('to-symbols');
			$('span[data-id=' + to_words + ']').html(cw);
			$('span[data-id=' + to_symbols + ']').html(cs);
			
		}

		$(trigger).each(function() {
			
			check_editor_words($(this));
			
		});
		
		$(document).on('change', trigger, function() {
			
			check_editor_words($(this));	
			
		});
		
		$(document).on('keyup', trigger, function() {
			
			check_editor_words($(this));
			
		});		
 
        return this;
    };
});

jQuery(function($) {	

    var defaults = { 
		trigger: '.js_editor_tag',
		push_editor: '',
		push_tag: '',
	};
	
    $.fn.EditorTags = function(method, params) {
		
        var options = $.extend({}, defaults, options, params);
        var now_obj = $(this);
 
		var trigger = $.trim(options['trigger']);
		var push_editor = options['push_editor'];
		var push_tag = $.trim(options['push_tag']);
		
		function setSelectionRange(input, selectionStart, selectionEnd) {
			if (input.setSelectionRange) {
				input.setSelectionRange(selectionStart, selectionEnd);
				input.focus();
			} else if (input.createTextRange) {
				var range = input.createTextRange();
				range.collapse(true);
				range.moveEnd('character', selectionEnd);
				range.moveStart('character', selectionStart);
				range.select();
			}
		}		

		function push_tag_editor(now_textarea, start_shortcode, end_shortcode = '', tag = '') {
			
			var section_start = parseInt(now_textarea.prop('selectionStart'));
			var section_end = parseInt(now_textarea.prop('selectionEnd'));
			var section_value = now_textarea.val();
			var new_value = '';
					
			if (end_shortcode.length > 0) { 
				if (section_start == section_end) {
					if (tag.hasClass('open_tag')) {
						
						tag.removeClass('open_tag');
						new_value = section_value.substr(0, section_start) + end_shortcode + section_value.substr(section_end, (section_value.length - section_start));
						now_textarea.val(new_value);
						setSelectionRange(now_textarea.get(0), (section_start + end_shortcode.length), (section_start + end_shortcode.length));
						
					} else {
						
						tag.addClass('open_tag');
						new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_end, (section_value.length - section_start));
						now_textarea.val(new_value);
						setSelectionRange(now_textarea.get(0), (section_start + start_shortcode.length), (section_start + start_shortcode.length));
						
					}
				} else {
					if (tag.hasClass('open_tag')) {
						
						tag.removeClass('open_tag');
						new_value = section_value.substr(0, section_end) + end_shortcode + section_value.substr(section_end, (section_value.length - section_end));
						now_textarea.val(new_value);
						setSelectionRange(now_textarea.get(0), (section_end + end_shortcode.length), (section_end + end_shortcode.length));
						
					} else {
						
						new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_start, (section_end - section_start)) + end_shortcode + section_value.substr(section_end, (section_value.length - section_end));
						now_textarea.val(new_value);
						setSelectionRange(now_textarea.get(0), (section_end + start_shortcode.length + end_shortcode.length), (section_end + start_shortcode.length + end_shortcode.length));
						
					}				
				}
			} else {
				
				new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_end, (section_value.length - section_start));
				now_textarea.val(new_value);
				setSelectionRange(now_textarea.get(0), (section_start + start_shortcode.length), (section_start + start_shortcode.length));
				
			}		
					
			now_textarea.trigger('change');
		}

		if (method == 'init') {
			
			$(document).on('click', trigger, function() {
				var editor_id = $(this).attr('to-editor-id');
				if (editor_id !== undefined) {
					var start_shortcode = $.trim($(this).find('.js_editor_tag_start').val());
					var end_shortcode = $.trim($(this).find('.js_editor_tag_end').val());
					var now_textarea = $('textarea[editor-id=' + editor_id + ']');
					push_tag_editor(now_textarea, start_shortcode, end_shortcode, $(this));
				}
				
				return false;
			});	
			
			$(document).on('click', '.js_editor_alltag', function() {
				var txt_hide = $(this).attr('data-hide');
				var txt_show = $(this).attr('data-show');
				var par = $(this).parents('.js_editor_tag_wrap');
				par.find('.js_editor_hidetag').hide();
				$(this).html(txt_show);
				if (!$(this).hasClass('active')) {
					par.find('.js_editor_hidetag').show();
					$(this).html(txt_hide);
				}
				$(this).toggleClass('active');
				
				return false;
			});
			
		}
		
		if (method == 'push') {
			
			push_tag_editor(push_editor, push_tag, '');
			
		}
 
        return this;
    };
});	