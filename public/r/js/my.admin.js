$.extend({
    parseQuery: function (query) {
        query = query.substring(1);
        var params = [];
        if (query) {
            var paramSegments = query.split('&');
            var segment = [];
            for (var i in paramSegments) {
                segment = paramSegments[i].split('=');
                if (segment.length == 1) {
                    params.push(segment[0]);
                } else if (segment.length == 2) {
                    params[segment[0]] = segment[1];
                } else if (segment.length > 2) {
                    var key = segment.shift();
                    params[key] = segment.join('=');
                }
            }
        }
        return params;
    },

    buildQuery: function (params) {
        var query = '';
        if (params) {
            var segments = [];
            for (var i in params) {
                if (!isNaN(i)) {
                    segments.push(params[i]);
                } else {
                    segments.push(i +'='+ params[i]);
                }
            }
            query = segments.join('&');
        }
        return query ? '?'+ query : '';
    },

    myAjax: function (method, url, params, options) {
        options = options || {};
        var defaults = {
            dataType: 'json',
            success: function (resp) {
                if (resp.code != undefined) {
                    if (resp.code == 0) {
                        alert('操作成功');
                        location.reload();
                    } else if (resp.msg != undefined && resp.msg) {
                        alert(resp.msg);
                    } else {
                        alert('操作失败');
                    }
                } else {
                    alert('返回结果异常');
                }
            },
            error: function (resp) {
                alert('请求失败');
            }
        };
        var opts = $.extend(defaults, options);
        if (method) {
            opts.type = method;
        }
        if (url) {
            opts.url = url;
        }
        if (params) {
            opts.data = params;
        }
        $.ajax(opts);
    },

    myAjaxGet: function (url, params, options) {
        $.myAjax('get', url, params, options);
    },

    myAjaxPost: function (url, params, options) {
        $.myAjax('post', url, params, options);
    }
});

;(function ($) {
    $.fn.pager = function () {
        var $this = $(this);
        var total = $this.data('total');
        var itemCount = $this.data('item-count');
        var defaultPageSize = $this.data('page-size');
        if (defaultPageSize == undefined || defaultPageSize <= 0) {
            defaultPageSize = 20;
        }
        var isSimplePager = $this.data('simple-pager') != undefined;
        var displayPageNum = $this.data('display-page');
        if (displayPageNum == undefined ||displayPageNum <= 0) {
            displayPageNum = 5;
        } else {
            displayPageNum = parseInt(displayPageNum);
        }
        var html = '';
        var params = $.parseQuery(location.search);
        var pageSize = params['count'] == undefined || params['count'] <= 0 ? defaultPageSize : parseInt(params['count']);
        var maxPage = total != undefined && total > 0 ? Math.ceil(total / pageSize) : 0;
        var curPage = params['page'] == undefined  || params['page'] <= 0 ? 1 : parseInt(params['page']);
        if (maxPage > 0 && curPage > maxPage) {
            curPage = maxPage;
        }

        //prev
        if (curPage <= 1) {
            params['page'] = 1;
        } else {
            params['page'] = curPage - 1;
        }
        var prevLink = location.pathname + $.buildQuery(params);

        //next
        if (curPage <= 1) {
            params['page'] = 2;
        } else {
            var nextPage = curPage + 1;
            if (maxPage > 0 && nextPage > maxPage) {
                nextPage = maxPage;
            }
            params['page'] = nextPage;
        }
        var nextLink = location.pathname + $.buildQuery(params);

        var disabledPrevPage = curPage == 1;
        var disabledNextPage = itemCount == 0;
        if (isSimplePager) {
            //仅有上一页/下一页的简单分页条
            html = '<ul class="pager mt-pager">' +
                '<li'+ (disabledPrevPage ? ' class="disabled"' : '') +'>' +
                '<a'+ (disabledPrevPage ? '' : ' href="'+ prevLink +'"') +'>上一页</a>' +
                '</li>' +
                '<li'+ (disabledNextPage ? ' class="disabled"' : '') +'>' +
                '<a'+ (disabledNextPage ? '' : ' href="'+ nextLink +'"') +'>下一页</a>' +
                '</li>' +
                '</ul>';
        } else {
            var interval = Math.ceil(displayPageNum / 2) - 1;

            //计算左边界
            var startPage = curPage - interval;
            if (startPage < 1) {
                startPage = 1;
            }
            //计算右边界
            var endPage = startPage + displayPageNum - 1;
            if (endPage > maxPage) {
                endPage = maxPage;
                if (startPage > 1) {
                    //重新计算左边界
                    startPage = endPage - displayPageNum + 1;
                    if (startPage < 1) {
                        startPage = 1;
                    }
                }
            }
            if (!disabledNextPage) {
                disabledNextPage = curPage == maxPage;
            }

            html = '<ul class="pager mt-pager">' +
                '<li'+ (disabledPrevPage ? ' class="disabled"' : '') +'>' +
                '<a'+ (disabledPrevPage ? '' : ' href="'+ prevLink +'"') +'>上一页</a>' +
                '</li>';
            if (startPage > 1) {
                //首页
                params['page'] = 1;
                html += '<li><a href="'+ location.pathname + $.buildQuery(params) +'">1</a></li>';
                if (startPage > 2) {
                    html += '<li><a>...</a></li>';
                }
            }
            for (var i = startPage; i <= endPage; i++) {
                params['page'] = i;
                html += '<li'+ (i == curPage ? ' class="active"' : '') +'><a href="'+ location.pathname + $.buildQuery(params) +'">'+ i +'</a></li>';
            }
            if (endPage < maxPage) {
                //尾页
                if (endPage < maxPage - 1) {
                    html += '<li><a>...</a></li>';
                }
                params['page'] = maxPage;
                html += '<li><a href="'+ location.pathname + $.buildQuery(params) +'">'+ maxPage +'</a></li>';
            }
            html += '<li'+ (disabledNextPage ? ' class="disabled"' : '') +'>' +
                '<a'+ (disabledNextPage ? '' : ' href="'+ nextLink +'"') +'>下一页</a>' +
                '</li>' +
                '</ul>';
        }
        $this.html(html);
    };

    $.fn.myAjaxForm = function (options) {
        var defaults = {
            dataType: 'json',
            success: function (resp) {
                if (resp.code != undefined) {
                    if (resp.code == 0) {
                        alert('操作成功');
                        location.reload();
                    } else if (resp.msg != undefined && resp.msg) {
                        alert(resp.msg);
                    } else {
                        alert('操作失败');
                    }
                } else {
                    alert('返回结果异常');
                }
            },
            error: function (resp) {
                alert('请求失败');
            }
        };
        var opts = $.extend(defaults, options);
        $(this).ajaxForm(opts);
    };

    /*$.extend({
        formAjaxUpload: function (json_data) {
            // FormData 对象
            var form = new FormData();
            // 文件对象
            form.append("upload", document.getElementById(json_data.id).files[0]);
            // XMLHttpRequest 对象
            var xhr = new XMLHttpRequest();
            xhr.open("post", json_data.url, true);
            xhr.onload = function (e) {
                if (this.status === 200) {
                    var data = this.responseText;
                    data = JSON.parse(data);
                    json_data.success(data);
                } else {
                    json_data.error(xhr.status, xhr);
                }
            };
            xhr.send(form);
        }
    });*/
})(jQuery);