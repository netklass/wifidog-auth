function MM_findObj(n, d) { //v4.0
var p,i,x;
if(!d) d=document;
if((p=n.indexOf("?"))>0&&parent.frames.length) {
d=parent.frames[n.substring(p+1)].document;
n=n.substring(0,p);
}
if(!(x=d[n])&&d.all) x=d.all[n];
for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
if(!x && document.getElementById) x=document.getElementById(n); return x;
}
                        
function changestyle(couche, style) {
if (!(layer = MM_findObj(couche))) return;
if(layer.className != "fpr_popup_inner_div_expanded")
{
layer.style.visibility = style;
}
}
                        
function changeclass(objet, myClass)
{
objet.className = myClass;
}
                        
function toggleItemVisibility(item_id)
{
if (!(layer = MM_findObj(item_id))) return;
if(layer.className != "fpr_popup_inner_div_expanded")
{
layer.style.visibility = "visible";
layer.className = "fpr_popup_inner_div_expanded";
}
else
{
layer.style.visibility = "hidden";
layer.className = "fpr_popup_inner_div";
}
}