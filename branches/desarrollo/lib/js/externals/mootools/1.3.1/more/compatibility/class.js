// MooTools: the javascript framework.
// Load this file's selection again by visiting: http://mootools.net/more/6f374bba69649270d07a0905eccffc2f 
// Or build this file again with packager using: packager build More/Events.Pseudos More/Class.Refactor More/Class.Binds More/Class.Occlude More/Chain.Wait
/*
---
copyrights:
  - [MooTools](http://mootools.net)

licenses:
  - [MIT License](http://mootools.net/license.txt)
...
*/
MooTools.More={version:"1.3.1.1",build:"0292a3af1eea242b817fecf9daa127417d10d4ce"};Events.Pseudos=function(g,c,e){var b="monitorEvents:";var a=function(h){return{store:h.store?function(i,j){h.store(b+i,j);
}:function(i,j){(h.$monitorEvents||(h.$monitorEvents={}))[i]=j;},retrieve:h.retrieve?function(i,j){return h.retrieve(b+i,j);}:function(i,j){if(!h.$monitorEvents){return j;
}return h.$monitorEvents[i]||j;}};};var f=function(j){if(j.indexOf(":")==-1||!g){return null;}var i=Slick.parse(j).expressions[0][0],m=i.pseudos,h=m.length,k=[];
while(h--){if(g[m[h].key]){k.push({event:i.tag,value:m[h].value,pseudo:m[h].key,original:j});}}return k.length?k:null;};var d=function(h){return Object.merge.apply(this,h.map(function(i){return g[i.pseudo].options||{};
}));};return{addEvent:function(m,p,j){var n=f(m);if(!n){return c.call(this,m,p,j);}var k=a(this),s=k.retrieve(m,[]),h=n[0].event,t=d(n),o=p,i=t[h]||{},l=Array.slice(arguments,2),r=this,q;
if(i.args){l.append(Array.from(i.args));}if(i.base){h=i.base;}if(i.onAdd){i.onAdd(this);}n.each(function(u){var v=o;o=function(){(i.listener||g[u.pseudo].listener).call(r,u,v,arguments,q,t);
};});q=o.bind(this);s.include({event:p,monitor:q});k.store(m,s);c.apply(this,[m,p].concat(l));return c.apply(this,[h,q].concat(l));},removeEvent:function(l,n){var m=f(l);
if(!m){return e.call(this,l,n);}var j=a(this),o=j.retrieve(l);if(!o){return this;}var h=m[0].event,p=d(m),i=p[h]||{},k=Array.slice(arguments,2);if(i.args){k.append(Array.from(i.args));
}if(i.base){h=i.base;}if(i.onRemove){i.onRemove(this);}e.apply(this,[l,n].concat(k));o.each(function(q,r){if(!n||q.event==n){e.apply(this,[h,q.monitor].concat(k));
}delete o[r];},this);j.store(l,o);return this;}};};(function(){var b={once:{listener:function(e,f,d,c){f.apply(this,d);this.removeEvent(e.event,c).removeEvent(e.original,f);
}},throttle:{listener:function(d,e,c){if(!e._throttled){e.apply(this,c);e._throttled=setTimeout(function(){e._throttled=false;},d.value||250);}}},pause:{listener:function(d,e,c){clearTimeout(e._pause);
e._pause=e.delay(d.value||250,this,c);}}};Events.definePseudo=function(c,d){b[c]=Type.isFunction(d)?{listener:d}:d;return this;};Events.lookupPseudo=function(c){return b[c];
};var a=Events.prototype;Events.implement(Events.Pseudos(b,a.addEvent,a.removeEvent));["Request","Fx"].each(function(c){if(this[c]){this[c].implement(Events.prototype);
}});}).call(this);Class.refactor=function(b,a){Object.each(a,function(e,d){var c=b.prototype[d];if(c&&c.$origin){c=c.$origin;}b.implement(d,(typeof e=="function")?function(){var f=this.previous;
this.previous=c||function(){};var g=e.apply(this,arguments);this.previous=f;return g;}:e);});return b;};Class.Mutators.Binds=function(a){if(!this.prototype.initialize){this.implement("initialize",function(){});
}return a;};Class.Mutators.initialize=function(a){return function(){Array.from(this.Binds).each(function(b){var c=this[b];if(c){this[b]=c.bind(this);}},this);
return a.apply(this,arguments);};};Class.Occlude=new Class({occlude:function(c,b){b=document.id(b||this.element);var a=b.retrieve(c||this.property);if(a&&!this.occluded){return(this.occluded=a);
}this.occluded=false;b.store(c||this.property,this);return this.occluded;}});(function(){var a={wait:function(b){return this.chain(function(){this.callChain.delay(b==null?500:b,this);
}.bind(this));}};Chain.implement(a);if(this.Fx){Fx.implement(a);}if(this.Element&&Element.implement&&this.Fx){Element.implement({chains:function(b){Array.from(b||["tween","morph","reveal"]).each(function(c){c=this.get(c);
if(!c){return;}c.setOptions({link:"chain"});},this);return this;},pauseFx:function(c,b){this.chains(b).get(b||"tween").wait(c);return this;}});}}).call(this);
