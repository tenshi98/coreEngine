<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section dashboard">
    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="row">

                <div class="col-sm-6 col-xl-4">

                    <div class="card">
                        <div class="card-header">Header</div>
                        <div class="card-body">
                            <?php

$widget  = '<link type="text/css" rel="stylesheet" href="'.$BASE.'/vendor/jstree/style.min.css" />';
$widget .= '<script type="text/javascript"          src="'.$BASE.'/vendor/jstree/jstree.min.js"></script>';
echo $widget;
                            ?>

<div id="jstree-1">
    <ul>
        <li>
            Root node 1
            <ul>
                <li data-jstree='{ "selected" : true, "icon" : "ri-home-4-line text-success " }'>
                    <a href="javascript:;">
                    Initially selected </a>
                </li>
                <li data-jstree='{ "icon" : "ri-home-4-line text-success " }'>
                    custom icon URL
                </li>
                <li data-jstree='{ "opened" : true }'>
                    initially open
                    <ul>
                        <li data-jstree='{ "disabled" : true }'>
                            Disabled Node
                        </li>
                        <li data-jstree='{ "type" : "file" }'>
                            Another node
                        </li>
                    </ul>
                </li>
                <li data-jstree='{ "icon" : "ri-vip-diamond-line text-danger" }'>
                    Custom icon class (bootstrap)
                </li>
            </ul>
        </li>
        <li data-jstree='{ "type" : "link" }'>
            <a href="http://www.coderthemes.com">
            Clickable link node </a>
        </li>
    </ul>
</div>

<script>
    !function(i){"use strict";function e(){this.$body=i("body")}e.prototype.init=function(){i("#jstree-1").jstree({core:{themes:{responsive:!1}},types:{default:{icon:"ri-folder-line"},file:{icon:"ri-article-line"}},plugins:["types"]}),i("#jstree-1").on("select_node.jstree",function(e,t){t=i("#"+t.selected).find("a");if("#"!=t.attr("href")&&"javascript:;"!=t.attr("href")&&""!=t.attr("href"))return"_blank"==t.attr("target")&&(t.attr("href").target="_blank"),document.location.href=t.attr("href"),!1}),i("#jstree-2").jstree({core:{themes:{responsive:!1}},types:{default:{icon:"ri-folder-line text-warning"},file:{icon:"ri-article-line  text-warning"}},plugins:["types"]}),i("#jstree-2").on("select_node.jstree",function(e,t){t=i("#"+t.selected).find("a");if("#"!=t.attr("href")&&"javascript:;"!=t.attr("href")&&""!=t.attr("href"))return"_blank"==t.attr("target")&&(t.attr("href").target="_blank"),document.location.href=t.attr("href"),!1}),i("#jstree-3").jstree({plugins:["wholerow","checkbox","types"],core:{themes:{responsive:!1},data:[{text:"Same but with checkboxes",children:[{text:"initially selected",state:{selected:!0}},{text:"custom icon",icon:"ri-feedback-line text-danger"},{text:"initially open",icon:"ri-folder-line text-default",state:{opened:!0},children:["Another node"]},{text:"custom icon",icon:"ri-article-line text-warning"},{text:"disabled node",icon:"ri-close-circle-line text-success",state:{disabled:!0}}]},"And wholerow selection"]},types:{default:{icon:"ri-folder-line text-warning"},file:{icon:"ri-article-line  text-warning"}}}),i("#jstree-4").jstree({core:{themes:{responsive:!1},check_callback:!0,data:[{text:"Parent Node",children:[{text:"Initially selected",state:{selected:!0}},{text:"Custom Icon",icon:"ri-feedback-line text-danger"},{text:"Initially open",icon:"ri-folder-line text-success",state:{opened:!0},children:[{text:"Another node",icon:"ri-article-line text-warning"}]},{text:"Another Custom Icon",icon:"ri-article-line text-warning"},{text:"Disabled Node",icon:"ri-close-circle-line text-success",state:{disabled:!0}},{text:"Sub Nodes",icon:"ri-folder-line text-danger",children:[{text:"Item 1",icon:"ri-article-line text-warning"},{text:"Item 2",icon:"ri-article-line text-success"},{text:"Item 3",icon:"ri-article-line text-default"},{text:"Item 4",icon:"ri-article-line text-danger"},{text:"Item 5",icon:"ri-article-line text-info"}]}]},"Another Node"]},types:{default:{icon:"ri-folder-line text-primary"},file:{icon:"ri-article-line  text-primary"}},state:{key:"demo2"},plugins:["contextmenu","state","types"]}),i("#jstree-5").jstree({core:{themes:{responsive:!1},check_callback:!0,data:[{text:"Parent Node",children:[{text:"Initially selected",state:{selected:!0}},{text:"Custom Icon",icon:"ri-article-line text-danger"},{text:"Initially open",icon:"ri-folder-line text-success",state:{opened:!0},children:[{text:"Another node",icon:"ri-article-line text-warning"}]},{text:"Another Custom Icon",icon:"ri-line-chart-line text-warning"},{text:"Disabled Node",icon:"ri-close-circle-line text-success",state:{disabled:!0}},{text:"Sub Nodes",icon:"ri-folder-line text-danger",children:[{text:"Item 1",icon:"ri-article-line text-warning"},{text:"Item 2",icon:"ri-article-line text-success"},{text:"Item 3",icon:"ri-article-line text-default"},{text:"Item 4",icon:"ri-article-line text-danger"},{text:"Item 5",icon:"ri-article-line text-info"}]}]},"Another Node"]},types:{default:{icon:"ri-folder-line text-success"},file:{icon:"ri-article-line  text-success"}},state:{key:"demo2"},plugins:["dnd","state","types"]}),i("#jstree-6").jstree({core:{themes:{responsive:!1},check_callback:!0,data:{url:function(e){return e.id,"assets/data/ajax_demo_children.json"},data:function(e){return{id:e.id}}}},types:{default:{icon:"ri-folder-line text-primary"},file:{icon:"ri-article-line  text-primary"}},state:{key:"demo3"},plugins:["dnd","state","types"]})},i.JSTree=new e,i.JSTree.Constructor=e}(window.jQuery),function(){"use strict";window.jQuery.JSTree.init()}();

</script>

<style>
    .jstree-default {
  padding:2px 6px;
  height:auto
}
.jstree-default .jstree-clicked,
.jstree-default .jstree-hovered {
  background:#f6f7fb;
  -webkit-box-shadow:none;
  box-shadow:none
}
.jstree-default .jstree-anchor,
.jstree-default .jstree-icon,
.jstree-default .jstree-icon:empty {
  line-height:20px;
}
.jstree-default .jstree-icon,
.jstree-default .jstree-node {
  background-image:url(<?php echo $BASE.'/vendor/jstree/treeview.png'; ?>)
}
.jstree-default .jstree-last {
  background:0 0
}
.jstree-default .jstree-themeicon-custom {
  background-color:transparent;
  background-image:none;
  background-position:0 0
}
.jstree-default .jstree-disabled {
  color:#8a969c
}
.jstree-wholerow.jstree-wholerow-clicked,
.jstree-wholerow.jstree-wholerow-hovered {
  background:#f6f7fb
}
</style>

                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</section>
