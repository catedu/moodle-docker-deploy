$(document).ready(function () {
    $(function () {
        $('div[id^="tabset-"]').tabs();  // "tabify". requires jquery ui lib
    });
    var url = window.location.pathname;
    var index = url.lastIndexOf("/");
    var filename = window.location.pathname.substr(index);
    var phpSourceLink = '<a href="php_source.php?_fn=' + filename.replace('.php','') + '">PHP Source</a>';
    $('script.code').each(function (index) {
        if ($('pre.code').eq(index).length) {
            $('pre.code').eq(index).html('<code class="javascript">/********* Javascript Generated with phpChart **********/' + $(this).html() + '</code>');
        }
        else {
            var str = $(this).html();
            $('div.jqplot-target').eq(index).after($('<pre class="code"><code class="javascript">/********* Javascript Generated with phpChart **********/' + str + '</code></pre>'));

            // create jquery ui tabs. 
            // insert <div> before <pre>
            // add <ul><li> for tabs
            $('pre.code').eq(index)
                .before('<br />')
                .wrap('<div id="tabset-' + index + '">')
                .before('<ul><li><a href="#tabs-' + index + '">Javascript Source</a></li><li>' + phpSourceLink + '</li></ul>')
                .wrap('<div id="tabs-' + index + '">');

        }
    });

    $(document).unload(function () { $('*').unbind(); });

});


