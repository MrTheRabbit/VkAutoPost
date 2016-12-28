APP = window.APP || {};
APP.Controls = APP.Controls || {};
APP.Controls.Page = APP.Controls.Page || {};
APP.DefaultPageClass = APP.DefaultPageClass || {};


(function($, APP) {
    'use strict';

    APP.DefaultPageClass = Backbone.View.extend({

        el: 'body',
        
		defaultOptionsFiler: {
			addMore: true,
			showThumbs: true,
			captions: {
				button: 'Выберете изображения',
				feedback: 'Выберете изображения для загрузки',
				feedback2: 'изображения для загрузки',
				drop: 'ПЕРЕТАЩИТЕ СЮДА ИЗОБРАЖЕНИЕ',
				removeConfirmation: 'Вы действительно хотите удалить это изображение?',
				errors: {
					filesLimit: 'Только {{fi-limit}} изображения можно загрузить.',
					filesType: 'Можно загружать только изображения.',
					filesSize: '{{fi-name}} слишком большой! Пожалуйста, загрузите изображение не больше {{fi-maxSize}} Мб.',
					filesSizeAll: 'Изображения слишком большие! Пожалуйста, загрузите изображения не больше {{fi-maxSize}} Мб.'
				}
			}
		},
		
		initialize: function() {
		},
		
        render: function() {
		}
		
    });


    /**
     * Контроллер приложения, запускает контроллеры страниц
     **/
    APP.Controls.Application = Backbone.View.extend({

        el: 'body',
        
		events: {
			//'click .btn_group_add': 'groupAdd',
			//'click .btn_group_edit': 'groupEdit',
			'change #f_action': 'checkActionForTask'
		},
		
		initialize: function() {
			//console.log('Application initialize');
			//this.setAllPreventDefault();
			this.checkVkToken();
			this.checkActionForTask();
			this.$el.find('.hide').removeClass('hide').hide();
		},
		
		// Ставит заглушки на все ссылки #
        setAllPreventDefault: function(e) {
        	e.preventDefault();
		},

        render: function() {
		},
		
        /*groupAdd: function() {
        	window.location.href = '/groups/add';
		},
		
        groupEdit: function(e) {
        	window.location.href = '/groups/edit/'+$(e.target).data('id');
		},
		
        groupDelete: function(e) {
        	console.log('setActive');
		},*/
		
		checkActionForTask: function() {
			if (this.$el.find('#f_action').length) {
				var intV = this.$el.find('#f_action').val();
				this.$el.find('.show_for_action_2').hide();
				if (intV == 2) this.$el.find('.show_for_action_2').show();
			}
		},//\\ checkActionForTask
		
		checkVkToken: function() {
			var that = this;
			var strUrl = window.location.href;
			if (strUrl.indexOf('#access_token=') > 0) {
				var arrT = strUrl.split('#');
				if (arrT.length == 2) {
					var arrT2 = arrT[1].split('&');
					$.each(arrT2, function(intI, strV){
						if (strV.indexOf('access_token=') == 0) {
							var arrT3 = strV.split('=');
							if (arrT3.length == 2 && arrT3[1].length > 5) {
								that.$el.find('#f_access_token').val(arrT3[1]);
							}//\\ if
						}//\\ if
					});
				}//\\ if
			}//\\ 
		}//\\ checkVkToken
    });

    $(function() {
        new APP.Controls.Application();
    });

})(jQuery, window.APP);