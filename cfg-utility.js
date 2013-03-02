/*
 * Custom Field GUI Utility 3.3
 *
 * Copyright (c) Tomohiro Okuwaki and Tsuyoshi Kaneko
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Since:       2008-10-15
 * Last Update: 2013-03-01
 *
 * jQuery v1.7.1
 * Facebox 1.2
 * exValidation 1.3.0  (c)5509 (http://5509.me/)
 */

jQuery(function($){

    // 新しいメディアアップローダー対応 <START>
    var css_static = {
        'position':'static'
    };
    var css_required = {
        'position': 'absolute',
        'z-index': '9',
        'width': '580px',
        'left': '50%',
        'margin-left': '-290px'
    };

    var file_type;
    var admin_url = location.href;
    var images_url = admin_url.replace(/(http.+)(wp-admin)(.+)/,'$1') + 'wp-content/plugins/' + current_dir + '/images/';
    var cancel_png = images_url + 'cancel.png';
    var must_png = images_url + 'must.png';

    var $media;
    var $inp;
    $('.button.cfg-add-image').click(function(e) {
       e.preventDefault();

       // inputフィールドを取得
       $inp = $(this).closest('.inside').find('input.data');

       // 既にメディアアップローダーのインスタンスが存在する場合
       if ($media) {
         $media.open();
         return;
       }

       // メディアアップローダーのインスタンスを生成
       $media = wp.media({
         title: 'ファイルを選択',
         button: {
          text: 'カスタムフィールドに挿入'
         },
         // 複数ファイル選択を許可しない
         multiple: false
       });

       /**
        * メディア選択時のイベント
        */
       $media.on('select', function() {
         // 選択したメディア情報を取得
         var attachment = $media.state().get('selection').first().toJSON();

         // メディアのIDとURLをinputフィールドに設定
         $inp.val("[" + attachment.id  + "]" + attachment.url);

            // テキストフィールドにファイルの種類のアイコンとキャンセルボタンを表示
                var media_url = getMediaURL ($inp.val());
                var media_type = getMediaType (attachment.url);

                if (media_type) {
                    $inp
                        .css('background','url(' + images_url + media_type + '.png) no-repeat 3px center')
                        .css('padding-left','20px')
                        .end()
                        .find('a.image')
                            .attr('href',media_url)
                            .html('<img src="' + media_url + '" width="150" />');
                } else {
                    $inp.removeAttr('style');
                }
                $inp.parent().find('img.cancel').attr('src', cancel_png).show();
       });

       // メディアアップローダーを開く
       $media.open();
    });
    // 新しいメディアアップローダー対応 </END>

    // 必須項目の設定（exValidation用）
    $('div.must').each(function(){
        var self = $(this);
        if (self.hasClass('textfield') || self.hasClass('imagefield') || self.hasClass('filefield')) {
            self.find('input.data').addClass('chkrequired');
        } else if (self.hasClass('checkboxs')) {
            self.find('label').addClass('chkcheckbox');
        } else if (self.hasClass('multi_checkbox')) {
            self.find('span.multi_checkbox_wrapper').addClass('chkcheckbox');
        } else if (self.hasClass('radio')) {
            self.find('div.radio_wrapper').addClass('chkradio');
        } else if (self.hasClass('select')) {
            self.find('select').addClass('chkselect');
        } else if (self.hasClass('textarea')) {
            self.find('textarea').addClass('chkrequired');
        }
    });

// Functions

    function getMediaURL (str) {
        return str.replace(/^(\[[0-9]+\])(.+)/,'$2');
    }

    function getMediaType (str) {
        var media_type = str.match(/[a-z]{2,5}$/i);
        if ((media_type == 'pdf')||(media_type == 'PDF')) {
            media_type = 'pdf';
        } else if ((media_type == 'jpg')||(media_type == 'JPG')||(media_type == 'gif')||(media_type == 'GIF')||(media_type == 'png')||(media_type == 'PNG')) {
            media_type = 'image';
        } else if ((media_type == 'html')||(media_type == 'HTML')||(media_type == 'htm')||(media_type == 'HTM')||(media_type == 'shtml')||(media_type == 'SHTML')||(media_type == 'php')||(media_type == 'PHP')) {
            media_type = 'web';
        } else if ((media_type == 'atom')||(media_type == 'ATOM')||(media_type == 'rss')||(media_type == 'RSS')||(media_type == 'rdf')||(media_type == 'RDF')) {
            media_type = 'feed';
        } else if ((media_type == 'doc')||(media_type == 'DOC')||(media_type == 'docx')||(media_type == 'DOCX')) {
            media_type = 'word';
        } else if ((media_type == 'xls')||(media_type == 'XLS')||(media_type == 'xlsx')||(media_type == 'XLSX')) {
            media_type = 'excel';
        } else {
            media_type = 'file';
        }
        return media_type;
    }

    // getPageScroll() by quirksmode.com
    function getPageScroll() {
        var xScroll, yScroll;
        if (self.pageYOffset) {
            yScroll = self.pageYOffset;
            xScroll = self.pageXOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {   // Explorer 6 Strict
            yScroll = document.documentElement.scrollTop;
            xScroll = document.documentElement.scrollLeft;
        } else if (document.body) {// all other Explorers
            yScroll = document.body.scrollTop;
            xScroll = document.body.scrollLeft;
        }
        return new Array(xScroll,yScroll)
    }

    // Adapted from getPageSize() by quirksmode.com
    function getPageHeight() {
        var windowHeight
        if (self.innerHeight) {   // all except Explorer
            windowHeight = self.innerHeight;
        } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
            windowHeight = document.documentElement.clientHeight;
        } else if (document.body) { // other Explorers
            windowHeight = document.body.clientHeight;
        }
        return windowHeight
    }

    function get_thumb_url (id) {
        var imaze_size_item = $(id + ' tr.image-size td.field div.image-size-item:has(input:checked)');
        var thumb_size = imaze_size_item.find('label.help').text();
            thumb_size = thumb_size.replace(/(\()([0-9]+)([^0-9]+)([0-9]+)(\))/,'-$2x$4');

        var thumb_url = $(id + ' tr.url td.field button.urlfile').attr('title');
        var thumb_ext = thumb_url.match(/\.[a-z]{2,5}$/i);
            thumb_ext = thumb_ext[0].toLowerCase().toString();
            thumb_url = thumb_url.replace(/(\.[a-z]{2,5}$)/i,thumb_size + thumb_ext);
        return thumb_url;
    }

    // Multi Checkbox [start]
    $('div.multi_checkbox').each(function(){

        var self = $(this);

        var checkboxs  = self.find('input:checkbox');
        var data_elm = self.find('input.data');
        var data_val = data_elm.val();
        var data_def = self.find('span.default').text().replace(/[ 　]*#[ 　]*/g,',');

        var data_arry = new Array;
        if (data_val) {
            data_arry = data_val.split(',');
            checkboxs.val(data_arry);
        }

        checkboxs.click(function(){
            var data_arry = new Array;
            self.find('input:checked').each(function(){
                data_arry.push($(this).val());
            });
            self.find('input.data').val(data_arry.join());
        });
    });
    // Multi Checkbox [end]

    // 管理画面にサムネイルを表示 [start]
    $('div.imagefield').each(function(){
        var div = $(this);
        var imf_data = div.find('input.data');
        var imf_val = imf_data.val();
        var imf_cancel = div.find('img.cancel');

        if (imf_val) {
            imf_cancel.attr('src', cancel_png).show();

            var media_url = getMediaURL(imf_val);
            var media_type = getMediaType(media_url);

            imf_data.css({
                'background':'url(' + images_url + media_type + '.png) no-repeat 3px center',
                'padding-left':'20px'
            });
            div.find('a.image').attr('href', media_url).html('<img src="' + media_url + '" width="150" />');
        } else {
            imf_data.removeAttr('style');
        }

        imf_data.change(function(){
            var imf_val = $(this).val();

            if (imf_val) {
                var images_url = getMediaURL(imf_val);
                var media_type = getMediaType(media_url);
                $(this)
                    .css({
                        'background':'url(' + images_url + media_type + '.png) no-repeat 3px center',
                        'padding-left':'20px'
                    })
                    .next('img.cancel').attr('src', cancel_png).show();
            } else {
                $(this)
                    .removeAttr('style')
                    .nextAll('img.cancel').attr('src', '').hide();
            }

            $(this)
                .nextAll('span.thumb')
                .find('a.image').attr('href', media_url).html('<img src="' + media_url + '" width="150" />');
        });
    });
    // 管理画面にサムネイルを表示 [end]

    // 「キャンセル」ボタンを押したときの動作の設定 [start]
    $('img.cancel').live('click', function() {
        $(this)
            .next('span')
                .find('a.image').removeAttr('href')
                .end()
                .find('img').fadeOut('slow', function(){
                    $(this).remove();
                })
                .end()
            .end()
            .prev().val('').removeAttr('style')
            .end()
            .hide();
    });
    // 「キャンセル」ボタンを押したときの動作の設定 [end]

    // フォーカスしたテキストフィールドの初期値を消す [start]
    $('div.postbox.textfield').find('input.data').each(function(){
        $(this).focus(function(){
            var default_val = $(this).attr('title');
            var current_val = $(this).val();
            if (default_val == current_val) {
                $(this).val('');
            }
        });
        $(this).blur(function(){
            var default_val = $(this).attr('title');
            var current_val = $(this).val();
            if (current_val == '') {
                $(this).val(default_val);
            }
        });
    });
    // フォーカスしたテキストフィールドの初期値を消す [end]

    // for facebox.js
    $("a[rel*=facebox]").facebox();

});
