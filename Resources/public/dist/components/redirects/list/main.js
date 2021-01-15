define(["underscore","config","services/suluredirect/redirect-manager","services/suluredirect/redirect-router"],function(a,b,c,d){"use strict";var e={options:{},templates:{list:['<div class="dropzone-container"/>','<div class="list-toolbar-container"></div>','<div class="list-info"></div>','<div class="datagrid-container"></div>','<div class="dialog"></div>'].join("")},translations:{headline:"sulu_redirect.title",success:"sulu_redirect.import.success"}},f=function(a){var b={total:0,exceptions:[]};for(var c in a){for(var d in a[c].exceptions)a[c].exceptions[d].fileName=a[c].fileName;b.total+=a[c].total,b.exceptions=b.exceptions.concat(a[c].exceptions)}return b};return{defaults:e,header:function(){return{noBack:!0,toolbar:{buttons:{add:{options:{callback:function(){d.toAdd()}}},deleteSelected:{},"import":{options:{icon:"cloud-upload",title:"sulu_redirect.import",callback:function(){this.sandbox.emit("husky.dropzone.redirects.show-popup")}.bind(this)}}}}}},layout:{content:{width:"max"}},initialize:function(){this.render(),this.bindCustomEvents()},render:function(){this.$el.html(this.templates.list()),this.sandbox.sulu.initListToolbarAndList.call(this,"redirect-routes","/admin/api/redirect-routes/fields",{el:this.$find(".list-toolbar-container"),instanceName:"redirect-routes",template:this.sandbox.sulu.buttons.get({settings:{options:{dropdownItems:[{type:"columnOptions"}]}}})},{el:this.sandbox.dom.find(".datagrid-container"),url:"/admin/api/redirect-routes?sortBy=created&sortOrder=desc",storageName:"redirect-routes",searchInstanceName:"redirect-routes",searchFields:["source","sourceHost","target"],resultKey:"redirect-routes",instanceName:"redirect-routes",actionCallback:function(a){d.toEdit(a)}.bind(this),viewOptions:{table:{actionIconColumn:"source"}}}),this.sandbox.start([{name:"dropzone@husky",options:{el:this.sandbox.dom.find(".dropzone-container"),url:"/admin/redirects/import",method:"POST",paramName:"redirectRoutes",instanceName:"redirects"}}])},bindCustomEvents:function(){this.sandbox.on("husky.dropzone.redirects.files-added",this.filesAddedHandler.bind(this)),this.sandbox.on("husky.datagrid.redirect-routes.number.selections",function(a){var b=a>0?"enable":"disable";this.sandbox.emit("sulu.header.toolbar.item."+b,"deleteSelected",!1)}.bind(this)),this.sandbox.on("sulu.toolbar.delete",function(){this.sandbox.emit("husky.datagrid.redirect-routes.items.get-selected",this.deleteItems.bind(this))}.bind(this))},filesAddedHandler:function(a){this.sandbox.emit("husky.datagrid.redirect-routes.update");var b=f(a);if(0===b.exceptions.length)return this.sandbox.emit("sulu.labels.success.show",this.sandbox.util.sprintf(this.translations.success,b));var c=$("<div/>");this.$el.append(c),this.sandbox.start([{name:"redirects/list/import-overlay@suluredirect",options:{el:c,result:b}}])},deleteItems:function(b){this.sandbox.emit("sulu.header.toolbar.item.loading","deleteSelected"),c.deleteMultiple(b).then(function(){a.each(b,function(a){this.sandbox.emit("husky.datagrid.redirect-routes.record.remove",a)}.bind(this)),this.sandbox.emit("sulu.header.toolbar.item.enabled","deleteSelected")}.bind(this))}}});