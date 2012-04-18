/*
 * Custom Field GUI Utility 3.2
 *
 * Copyright (c) Tomohiro Okuwaki
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Since:       2008-10-15
 * Last Update: 2012-04-19
 *
 * jQuery v1.7.1
 * Facebox 1.2
 * exValidation 1.3.0  (c)5509 (http://5509.me/)
 */

jQuery(function($){

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
    
    function insert_btn (id) {
        var imf_ins_btns = 
            '<button title="#' + id + '" class="button imf_ins_url" type="button">カスタムフィールドに挿入</button>';

        return imf_ins_btns;
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

    // cookieのセット
    function setCookie(key, val, days){
        var cookie = escape(key) + "=" + escape(val);
        if(days != null){
            var expires = new Date();
            expires.setDate(expires.getDate() + days);
            cookie += ";expires=" + expires.toGMTString();
        }
        document.cookie = cookie;
    }
    
    // cookieの取得
    function getCookie(key) {
        if(document.cookie){
            var cookies = document.cookie.split(";");
            for(var i=0; i<cookies.length; i++){
                var cookie = cookies[i].replace(/\s/g,"").split("=");
                if(cookie[0] == escape(key)){
                    return unescape(cookie[1]);
                }
            }
        }
        return "";
    }

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
    var images_url = admin_url.replace(/(http.+)(wp-admin)(.+)/,'$1') + 'wp-content/plugins/custom-field-gui-utility/images/';
    var cancel_png = images_url + 'cancel.png';
    var must_png = images_url + 'must.png';

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

    // アップロードボタンを調整 [start]
    $('p.cfg_add_media_pointer a.add_media').addClass('cfg_add_media_clone').removeAttr('id');
    // アップロードボタンを調整 [end]

    // イメージフィールド・ファイルフィールド周りのliveイベントを設定 [start]
    $('a.cfg_add_media_clone').on('click', function(){
        var self = $(this);

        // アップローダーをクリック(clc)したイメージフィールドのidをcookieに保存
        var clc_id = self.closest('div.imagefield').attr('id');
        setCookie('imf_clc_id',clc_id);
    });
    // イメージフィールド・ファイルフィールド周りのliveイベントを設定 [end]

    // アップローダーにカスタムフィールド用ボタンを追加 [start]
    $('#media-items div.media-item').each(function(){
        var id = $(this).find('thead').attr('id');
        $(this).find('tr.submit td.savesend').prepend(insert_btn(id));
    }).live('mouseover', function(){
        var id = $(this).find('thead').attr('id');
        if (!($(this).find('tr.submit td.savesend button.imf_ins_url').length)){
            $(this).find('tr.submit td.savesend').prepend(insert_btn(id));
        }
    });
    // アップローダーにカスタムフィールド用ボタンを追加 [end]

    // カスタムフィールドに挿入するボタンのイベント [start]
    $('button.imf_ins_url').live('click', function(){
        var id = $(this).attr('title').replace(/#media-head-/,'');
        var media_url = $(this).closest('tr.submit').prevAll('tr.url').find('td.field input.urlfield').val();
        var imf_val = '[' + id + ']' + media_url;

        var parent_doc = this.ownerDocument.defaultView || this.ownerDocument.parentWindow;
        parent_doc = parent_doc.parent.document;

        var imf_clc_id = '#' + getCookie('imf_clc_id');
        var imf_elm = $(parent_doc).find(imf_clc_id);
        setCookie('imf_clc_id','');
        
        // カスタムフィールドに値を入れる
        if (imf_val) {
            imf_elm.find('input.data').val(imf_val);
            // テキストフィールドにファイルの種類のアイコンとキャンセルボタンを表示
            var media_url = getMediaURL (imf_val);
            var media_type = getMediaType (media_url);
            if (media_type) {
                imf_elm
                    .find('input.data')
                        .css('background','url(' + images_url + media_type + '.png) no-repeat 3px center')
                        .css('padding-left','20px')
                    .end()
                    .find('a.image')
                        .attr('href',media_url)
                        .html('<img src="' + media_url + '" width="150" />');
            } else {
                imf_elm.find('input.data').removeAttr('style');          
            }
            imf_elm.find('img.cancel').attr('src', cancel_png).show();
        }

        $('p.ml-submit input:submit').click();
    });
    // カスタムフィールドに「URL」を挿入するボタンのイベント [end]

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
