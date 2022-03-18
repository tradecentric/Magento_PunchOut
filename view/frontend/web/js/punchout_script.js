/* rv.2.1.20-1 */

define([
        'jquery',
        'mage/url',
        'Magento_Customer/js/customer-data',
        'mage/cookies'
    ],
    function ($,url,customerData) {
        'use strict';

        return {
            session_data: {
                "is_edit": 0,
                "is_punchout": false,
                "is_error": false,
                "selected_item": false,
                "posid": null,
                "config": {
                    "display": {
                        "return_link_label": "Return",
                        "return_link_enabled": null
                    },
                    "session": {
                        "load_posdelay": 0,
                        "js_clear_localdata": false,
                        "js_session_clean": false,
                        "js_reload_sections": null,
                        "use_js_redirection": false,
                        "edit_redirect_message": "Redirecting to your cart..",
                        "l2_redirect_message": "Redirecting to {name}..",
                        "redirect_timeout": null
                    },
                    "system": {
                        "js_logging": false
                    }
                }
            },

            "punchout" : function (settings)
            {
                this.init(settings);
            },

            init : function (session_config) {
                this.session_data.config = session_config;
                var delay = this.session_data.config.session.load_posdelay;
                if (delay > 0
                    || self.location.href.indexOf('posdelay') > 1) {
                    var delay_pos = self.location.href.indexOf('posdelay=');
                    var timeout = delay;
                    if (delay_pos > 0) {
                        var start = (delay_pos + 9);
                        var end = self.location.href.indexOf('&', start);
                        if (end > 1) {
                        } else {
                            end = self.location.href.length;
                        }
                        var len = end - start;
                        timeout = self.location.href.substr(start, len);
                    }
                    setTimeout($.proxy(function () {
                        this.loadSession();
                    }, this), timeout);
                } else if (delay < 0) {
                    // do not execute JS user load
                    // depend on class
                    if ($('body').hasClass('is-punchout-session')) {
                        this.session_data.is_punchout = 1;
                        var php_session = $.cookie('PHPSESSID');
                        if (php_session) {
                            this.session_data.posid = php_session;
                        } else {
                            this.session_data.posid = 1;
                        }
                        this.startUp();
                    }
                } else {
                    this.loadSession();
                }
            },

            buildUrl : function (path){
                return url.build(this.session_data.config.baseUrl + path);
            },

            loadSession : function ()
            {
                var data_url = this.buildUrl('punchout/session/data');
                // console.log("In load session", this.session_data.config);
                $.ajax(data_url, {
                    "dataType": "json",
                    "complete": $.proxy(function (connection_data) {
                        var data = connection_data.responseJSON;
                        if (typeof data == "undefined") {
                            this.session_data.is_punchout = false;
                            this.session_data.is_error = true;
                        } else {
                            this.session_data = data;
                        }

                        // console.log("DATA URL", data_url);
                        // console.log("DATA Returned", this.session_data);
                        this.startUp();
                    },this)
                });
            },

            startUp : function ()
            {
                if (this.isPunchout()) {
                    //this.log('is punchout',this.session_data);
                    if (!$('body').hasClass('is-punchout-session')) {
                        $('body').addClass('is-punchout-session');
                    }
                    this.updateDisplay();
                    this.firstPageCheck();
                }
            },

            updateDisplay : function ()
                {
                if (this.session_data.config.display.return_link_enabled) {
                    //$('li.punchout-close').show();
                    $('.punchout-close').addClass("on");
                    this.log('show close session');
                }
            },

            isPunchout : function ()
            {
                return this.session_data.is_punchout;
            },

            isLevel2 : function ()
            {
                if (this.session_data.selected_item == false) {
                    return false;
                } else {
                    return true;
                }
            },

            isEdit : function ()
            {
                return this.session_data.is_edit;
            },

            firstPageCheck : function ()
            {
                if (self.location.href.indexOf('force_first_load') > 1
                    || this.getLocalData('posid-first-load') != this.session_data.posid) {

                    this.log('is first page.');

                    if (this.cleanData()) {
                        this.redirectSession();
                    }

                }
            },

            redirectSession : function ()
            {
                if (this.session_data.config.session.use_js_redirection) {
                    this.log('use js redirect..');
                    if (this.isLevel2()) {
                        this.log('redirect level 2');
                        this.redirectToSelectedItem();
                    } else if (this.isEdit()) {
                        this.log('redirect edit');
                        this.redirectToCart();
                    } else {
                        this.log('no redirection');
                    }
                }
            },

            redirectToSelectedItem : function ()
            {
                var selected_item = this.session_data.selected_item;
                var redirect_message = this.session_data.config.session.l2_redirect_message;
                $.each(selected_item, function (key, value) {
                    var strkey = '{'+ key +'}';
                    redirect_message = redirect_message.replace(strkey, value);
                });
                Basic.Modal.show(redirect_message);
                var timeout = this.getRedirectTimeout();
                setTimeout(function() {
                    self.location.href = selected_item.url;
                },timeout);
                this.log(redirect_message +' '+ timeout, selected_item);
            },

            redirectToCart : function ()
            {
                var redirect_message = this.session_data.config.session.edit_redirect_message;
                Basic.Modal.show(redirect_message);
                var timeout = this.getRedirectTimeout();
                setTimeout(function() {
                    self.location.href = url.build('/checkout/cart');
                },timeout);
                this.log(redirect_message +' '+ timeout);
            },

            cleanData : function()
            {
                //console.log('clean storage');
                // set cookie, flag we are in.
                //$.cookie.delete('posid-first-load');
                this.setLocalData('posid-first-load',this.session_data.posid);

                if (this.session_data.config.session.js_clear_localdata) {
                    // clear mage storage
                    this.log('clear local storage');
                    var storage = $.initNamespaceStorage('mage-cache-storage').localStorage;
                    storage.removeAll();
                }

                if (this.session_data.config.session.js_session_clean
                    && this.isEdit()) {
                    this.log('execute punchout/session/clean');
                    var data_url = url.build('/punchout/session/clean');
                    $.ajax(data_url, {
                        "type" : "POST",
                        "data" : {},
                        "dataType": "json",
                        "complete": $.proxy(function (connection_data) {
                            this.clearCustomerData();
                            this.redirectSession();
                        },this)
                    });
                    return false;
                } else {
                    this.clearCustomerData();
                    return true;
                }
            },

            clearCustomerData : function ()
            {
                //require([
                //        'Magento_Customer/js/customer-data'
                //    ],
                //    function (customerData) {
                var reload_sections = this.session_data.config.session.js_reload_sections;
                this.log('reload customer data : '+ reload_sections)
                if (reload_sections) {
                    //var sections = ['cart','customer'];
                    var sections = reload_sections.split(",");
                    this.log('split',sections);
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                }
                //    }
                //);
            },

            getRedirectTimeout : function ()
            {
                var redirect_timeout = parseInt(this.session_data.config.session.redirect_timeout);
                return (typeof redirect_timeout == "number" ? redirect_timeout : 2000);
            },

            getLocalData : function (name)
            {
                var storage = $.initNamespaceStorage('punchout-data').localStorage;
                return storage.get(name);
            },

            setLocalData : function (name,value)
            {
                var storage = $.initNamespaceStorage('punchout-data').localStorage;
                return storage.set(name,value);
            },

            log : function (string,data)
            {
                if (this.session_data.config.system.js_logging) {
                    console.log(string,data);
                }
            }

        }

    }

);

/*
//    'underscore',
//    'ko',
//    'Magento_Customer/js/section-config',
//    'Magento_Customer/js/customer-data',
//    'jquery/jquery-storageapi'],
// _,ko,sectionConfig,customerData,storageApi

        $(function () {
            var Punchout = {

            }
            if (!$.cookieStorage.isSet('mage-cache-sessid')
                || ($.cookieStorage.isSet('punchout-reset-storage')
                    && $.cookieStorage.get('punchout-reset-storage') != 0)
                || self.location.href.indexOf('posid') > 0) {
                //self.alert('remove all..');
                //customerData.invalidate(['*']);
                $.cookieStorage.set('mage-cache-sessid', true);
                //self.alert('force invalidated..');
                var storage = $.initNamespaceStorage('mage-cache-storage').localStorage;
                storage.removeAll();
                //self.alert('removed all');
                //customerData.reload(['*'],true);
                $.cookieStorage.set('punchout-reset-storage', '0');
                //var sections = ['cart','customer'];
                if (self.location.href.indexOf('edit=1') > 0) {
                    if ($('.cart-empty')) {
                        $('.cart-empty').html('Retrieving your previous cart items...');
                        setTimeout(function () {
                            require([
                                    'Magento_Customer/js/customer-data'
                                ],
                                function (customerData) {
                                    var sections = ['cart','customer'];
                                    customerData.invalidate(sections);
                                    customerData.reload(sections, true);
                                }
                            );
                        },2000);
                    }
                }
            } else {
                //self.alert('keep local cache...');
            }
 */