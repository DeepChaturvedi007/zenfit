(window.webpackJsonp=window.webpackJsonp||[]).push([[9],{579:function(e,n,t){"use strict";t.r(n);var a,r,l,i,o,c=t(1),u=t(0),s=t.n(u),d=t(576),m=t(471),p=t(364),b=t(413),f=t(414),g=t(415),E=t(106),h=t(31),v=t(90),x={0:"Custom split",1:"50c/30p/20f",2:"40c/40p/20f",4:"10c/30p/60f",6:"35c/35p/30f"},O=t(37),j=t(17),y=1,k=2,w=s.a.lazy(function(){return t.e(7).then(t.bind(null,577))}),C=s.a.lazy(function(){return t.e(6).then(t.bind(null,578))}),S=Object(u.memo)(function(){var e=E.a.useContainer(),n=e.current,t=e.flush;switch(n.type){case v.a.PDF_DOWNLOAD:return s.a.createElement(u.Suspense,{fallback:null},s.a.createElement(w,Object.assign({},n.props,{onFlush:t})));case v.a.MEAL_MACRO_SPLIT:return s.a.createElement(u.Suspense,{fallback:null},s.a.createElement(C,Object.assign({},n.props,{onFlush:t})));default:return null}}),T=t(377),P=t(30),D=t(378),_=t(416),M=t(56),A=t(4),R=Object(u.memo)(function(e){var n=e.dish,t=e.index,a=e.onShowRecipes,r=e.onRemove,l=e.onPlanView,i=Math.round(Math.abs(n.ideal_kcals-n.totals.kcal));return s.a.createElement(T.b,{draggableId:n.id,index:t},function(e,t){return s.a.createElement(M.a,Object.assign({ref:e.innerRef},e.draggableProps,e.dragHandleProps),s.a.createElement(M.e,null,s.a.createElement(M.d,{src:n.image}),i>30&&s.a.createElement(M.f,null,s.a.createElement(_.a,null),s.a.createElement("span",null,i," kcals off"))),s.a.createElement(M.c,null,s.a.createElement("h5",null,n.name),s.a.createElement("span",null,n.totals.kcal," kcals")),s.a.createElement(M.b,null,s.a.createElement(A.j,{onClick:a},"restaurant"),s.a.createElement(A.o,null),s.a.createElement(A.j,{onClick:l},"remove_red_eye"),s.a.createElement(A.o,null),s.a.createElement(A.j,{onClick:r},"close")),s.a.createElement(M.g,{hidden:t.isDragging},n.totals.kcal," kcals: ",n.name))})}),I=t(91),Y=t(26),F=t(7),L=t(13),z=t(2),N=t(10),B=t.n(N),V=t(392),q=t.n(V),K=t(75),U=t(76),W=t(79),G=t(78),X=t(49),J=t(16);function $(e){var n=function(){if("undefined"===typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"===typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],function(){})),!0}catch(e){return!1}}();return function(){var t,a=Object(X.a)(e);if(n){var r=Object(X.a)(this).constructor;t=Reflect.construct(a,arguments,r)}else t=a.apply(this,arguments);return Object(G.a)(this,t)}}function H(e){e.stopPropagation(),e.preventDefault()}var Q=z.b.div(a||(a=Object(c.a)(["\n  background: #2795f1;\n  border-radius: 3px;\n  display: block;\n  position: absolute;\n  height: 100%;\n  top: 0;\n"]))),Z=z.b.div(r||(r=Object(c.a)(["\n  background-color: #fff;\n  border: 1px solid #e6ebf1;\n  border-radius: 3px;\n  cursor: pointer;\n  display: inline-block;\n  position: absolute;\n  box-shadow: 0 1px 3px rgba(0,0,0,.08);\n  width: 42px;\n  height: 18px;\n  top: -6px;\n  user-select: none;\n  text-align: center;\n  \n  :active {\n    box-shadow: 0 0 3px rgba(0,0,0,0.2);\n    cursor: grabbing;\n    cursor: -moz-grabbing;\n    cursor: -webkit-grabbing;\n  }\n"]))),ee=z.b.span(l||(l=Object(c.a)(["\n  color: #32325d;\n  display: inline-block;\n  padding: 2px 4px;\n  vertical-align: top;\n  line-height: 1;\n  font-size: 12px;\n  font-weight: 500;\n"]))),ne=z.b.div(i||(i=Object(c.a)(["\n  background: #e6ebf1;\n  border-radius: 3px;\n  display: block;\n  min-width: 140px;\n  height: 6px;\n  margin: 8px 0;\n  position: relative;\n  \n  ","\n"])),function(e){return e.disabled&&Object(z.a)(o||(o=Object(c.a)(["\n    "," {\n      background: transparent;\n    }\n\n    "," {\n      background-color: #f4f4f4;\n      box-shadow: none;\n      border-color: #E2E2E2;\n    }\n  "])),Q,Q)}),te=function(e){Object(W.a)(t,e);var n=$(t);function t(e){var a;return Object(K.a)(this,t),(a=n.call(this,e)).setup=function(){var e=Object(J.findDOMNode)(a.slider.current).offsetWidth,n=Object(J.findDOMNode)(a.handle.current).offsetWidth;a.setState({limit:e-n,grab:n/2})},a.onKnobMouseDown=function(){document.addEventListener("mousemove",a.onDragStart),document.addEventListener("touchmove",a.onDragStart),document.addEventListener("mouseup",a.onDragEnd),document.addEventListener("touchend",a.onDragEnd)},a.onDragStart=function(e){var n=a.props.onChange,t=a.position(e);a.setState({value:t},function(){n&&n(t)})},a.onDragEnd=function(){document.removeEventListener("mousemove",a.onDragStart),document.removeEventListener("touchmove",a.onDragStart),document.removeEventListener("mouseup",a.onDragEnd),document.removeEventListener("touchend",a.onDragEnd)},a.slider=Object(u.createRef)(),a.handle=Object(u.createRef)(),a.state={limit:0,grab:0,value:e.value},a}return Object(U.a)(t,[{key:"componentDidMount",value:function(){this.setup(),window.addEventListener("resize",this.setup)}},{key:"componentWillUnmount",value:function(){window.removeEventListener("resize",this.setup)}},{key:"render",value:function(){var e=this.props.labelRenderer,n=this.getPositionFromValue(this.state.value),t=this.coordinates(n),a={width:"".concat(t.fill,"px")},r={left:"".concat(t.handle,"px")},l=0===this.props.max||this.props.isDisabled,i=e?e(this.state.value):null;return s.a.createElement(ne,{ref:this.slider,disabled:l},s.a.createElement(Q,{style:a}),s.a.createElement(Z,{ref:this.handle,onMouseDown:l?H:this.onKnobMouseDown,onTouchStart:l?H:this.onKnobMouseDown,style:r},i&&s.a.createElement(ee,null,i)))}},{key:"getPositionFromValue",value:function(e){var n=this.state.limit,t=this.props,a=t.min,r=t.max-a,l=0!==r?(e-a)/r:.5;return Math.round(l*n)}},{key:"getValueFromPosition",value:function(e){var n=this.state.limit,t=this.props,a=t.min,r=t.max,l=t.step,i=function(e,n,t){return e<n?n:e>t?t:e}(e,0,n)/(n||1);return l*Math.round(i*(r-a)/l)+a}},{key:"position",value:function(e){var n=Object(J.findDOMNode)(this.slider.current),t=(e.touches?e.touches[0].clientX:e.clientX)-n.getBoundingClientRect().left-this.state.grab;return this.getValueFromPosition(t)}},{key:"coordinates",value:function(e){var n=this.getValueFromPosition(e),t=this.getPositionFromValue(n);return{fill:t+this.state.grab,handle:t}}}],[{key:"getDerivedStateFromProps",value:function(e,n){return e.value!==n.value?{value:e.value}:null}}]),t}(u.PureComponent);te.defaultProps={min:0,max:100,step:1,value:0,isDisabled:!1,onChange:function(e){},labelRenderer:function(e){return e}};var ae,re,le,ie,oe,ce,ue,se,de,me,pe,be,fe,ge,Ee,he,ve,xe,Oe,je,ye,ke,we,Ce,Se,Te,Pe,De,_e,Me,Ae,Re,Ie,Ye,Fe=te,Le=t(62),ze=Object(z.b)(Le.d)(ae||(ae=Object(c.a)(["\n  display: flex;\n  justify-content: space-between;\n  margin: 5px 0;\n"]))),Ne=z.b.span(re||(re=Object(c.a)(["\n  display: inline-block;\n  font-family: Roboto, sans-serif;\n  font-size: 12px;\n  font-weight: normal;\n  font-stretch: normal;\n  font-style: normal;\n  line-height: normal;\n  letter-spacing: 0.05px;\n  color: #333333;\n  padding: 5px;\n  border-radius: 2px;\n  background-color: #f5f5f5;\n"]))),Be=function(e){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:100,t=parseFloat(e);"number"===typeof t&&isFinite(t)||(t=0);return"".concat((t*n).toFixed(0),"%")},Ve=z.b.div(le||(le=Object(c.a)(["\n  width: 232px;\n  padding: 0 5px;\n\n  "," {\n    margin-bottom: 16px;\n  }\n"])),Le.c),qe=z.b.div(ie||(ie=Object(c.a)(["\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  margin-top: 20px;\n"]))),Ke=z.b.span(oe||(oe=Object(c.a)(["\n  background-color: ",";\n  border-radius: 4px;\n  display: inline-block;\n  line-height: 1;\n  color: #fff;\n  font-size: 14px;\n  padding: 6px 10px;\n  font-weight: 500;\n"])),function(e){return e.isDanger?"#d14":"#32325d"}),Ue=Object(u.memo)(function(e){var n=e.meal,t=e.planId,a=P.a.useContainer(),r=Object(u.useRef)(),l=Object(u.useState)([]),i=Object(L.a)(l,2),o=i[0],c=i[1],d=Object(u.useState)(!1),m=Object(L.a)(d,2),p=m[0],b=m[1],f={min:.1,max:1,step:.01,labelRenderer:Be},g=Object(u.useMemo)(function(){return o.reduce(function(e,n){return e+n.value},0)},[o]),E=Object(u.useMemo)(function(){return 100!==Math.round(100*g)},[g]),h=function(e){return function(n){c(B()(o,Object(F.a)({},e,{value:{$set:Math.round(100*n)/100}})))}},v=Object(u.useCallback)(function(){E||(b(!0),q()(function(){var e=o.map(function(e){return{id:e.id,value:e.value}});a.updateProgressWeights(t,e).then(function(){b(!1),q()(r.current.close,50)})},250))},[o]),x=Object(u.useCallback)(function(){return s.a.createElement(A.l,{href:"#"},Be(n.percent_weight))},[n.percent_weight]),O=Object(u.useCallback)(function(){return s.a.createElement(Ve,null,o.map(function(e,n){return s.a.createElement(Le.c,{key:"input_".concat(n)},s.a.createElement(ze,null,e.name,s.a.createElement(Ne,null,Math.round(e.kcalsPerPercent*e.value*100)," kcal")),s.a.createElement(Fe,Object.assign({value:e.value,onChange:h(n)},f)))}),s.a.createElement(qe,null,s.a.createElement(Ke,{isDanger:E},Be(g)),s.a.createElement(A.c,{disabled:E||p,onClick:v,type:"button"},p?"Saving...":"Save")))},[o,p,E]);return s.a.createElement(I.a,{ref:r,canClose:!p,renderTrigger:x,renderBody:O,onOpen:function(){c(B()(o,{$set:Object(Y.a)(a.progressWeightsByPlan(t))}))},style:{zIndex:110}})}),We=t(48),Ge=Object(z.c)(ce||(ce=Object(c.a)(["\n  0% {\n    transform: rotate(0deg);\n  }\n  100% {\n    transform: rotate(360deg);\n  }\n"]))),Xe=z.b.div(ue||(ue=Object(c.a)(["\n  padding: 8px 0 16px;\n  \n  ","\n"])),We.a.desktop(se||(se=Object(c.a)(["\n    padding: 10px 16px;\n    transform: translateY(50%);\n    text-align: center;\n    padding: 0;\n  "])))),Je=z.b.div(de||(de=Object(c.a)(["\n  backface-visibility: hidden;\n  padding: 12px 0;\n  text-align: left;\n\n\n  width: 100%;\n\n  ","\n"])),We.a.desktop(me||(me=Object(c.a)(["\n    border-bottom: 1px solid #f2f6fa;  \n    padding: 12px 16px;\n  "])))),$e=z.b.div(pe||(pe=Object(c.a)(["\n  display: flex;\n  justify-content: space-between;\n  align-items: flex-end;\n"]))),He=z.b.div(be||(be=Object(c.a)(["\n  color: #8898aa;\n  display: flex;\n  justify-content: space-between;\n  font-size: 13px;\n"]))),Qe=z.b.h5(fe||(fe=Object(c.a)(["\n  color: #32325d;\n  margin: 0;\n  text-transform: capitalize;\n  font-weight: 600;\n  font-size: 14px;\n"]))),Ze=z.b.div(ge||(ge=Object(c.a)(["\n  display: flex;\n  flex-direction: column;\n  flex-wrap: wrap;\n  width: 100%;\n  transition: background-color .2s linear;\n  margin-bottom: 16px;\n  border-color: #f2f6fa;\n  border-style: solid;\n  border-radius: 4px;\n\n  &:not(:last-child) {\n    border-width: 0 0 1px 0;\n  }\n\n  &:last-child {\n    border-width: 0;\n  }\n  \n  ","\n\n  ","\n"])),function(e){return e.disabled&&Object(z.a)(Ee||(Ee=Object(c.a)(["\n    pointer-events: none;\n  "])))},We.a.desktop(he||(he=Object(c.a)(["\n    flex-direction: column;\n    border-radius: 0;\n    border-width: 0 1px 0 0;\n    margin-bottom: 0;\n\n    ","\n    \n    :first-child {\n      border-radius: 4px 0 0 4px;\n    }\n\n    :last-child {\n      border-radius: 0 4px 4px 0;\n      border-right-width: 0;\n    }\n  "])),function(e){return!e.disabled&&Object(z.a)(ve||(ve=Object(c.a)(["\n      :hover {\n        background: #f2f6fa !important;\n  \n        "," {\n          border-bottom-color: #fff;\n        }\n      }\n    "])),Je)})),en=z.b.div(xe||(xe=Object(c.a)(["\n  background-color: ",";\n  box-sizing: border-box;\n  \n  display: flex;\n  flex-direction: row;\n  flex-wrap: wrap;\n  flex: 1;\n  margin: 0 -8px;\n  min-height: 100px;\n\n  ","\n  \n  ","\n"])),function(e){return e.isDraggingOver?"#f6fff5":"transparent"},We.a.desktop(Oe||(Oe=Object(c.a)(["\n    flex-direction: column;\n    align-items: flex-start;\n    padding: 16px 16px 26px;\n    margin: 0;\n    width: 100%;\n  "]))),function(e){return e.loading&&Object(z.a)(je||(je=Object(c.a)(['\n    position: relative;\n    \n    ::before,\n    ::after {\n      content: "";\n      cursor: wait;\n      position: absolute;\n      pointer-events: none;\n      z-index: 50;\n    }\n    \n    ::before {\n      background-color: rgba(255, 255, 255, .9);\n      top: 0;\n      left: 0;\n      right: 0;\n      bottom: 0;\n    }\n    \n    ::after {\n      animation: ',' .5s infinite linear;\n      border: 2px solid #8898aa;\n      border-radius: 50%;\n      border-right-color: transparent;\n      border-top-color: transparent;\n      content: "";\n      display: block;\n      height: 24px;\n      width: 24px;\n      left: 50%;\n      margin-left: -12px;\n      margin-top: -12px;\n      position: absolute;\n      top: 50%;\n    }\n  '])),Ge)}),nn=z.b.div(ye||(ye=Object(c.a)(["\n  display: flex;\n  flex-direction: column;\n\n  ","\n"])),We.a.desktop(ke||(ke=Object(c.a)(["\n    margin-bottom: 26px;\n    border: 1px solid #f2f6fa;\n    border-radius: 4px;\n    flex-direction: row;\n  "])))),tn=Object.keys(x).slice(1).map(function(e){return{value:parseInt(e,10),label:x[e]}}),an=s.a.memo(function(e){var n=e.meal,t=e.planId,a=e.planType,r=e.onRemoveDish,l=e.isLoading,i=e.viewMealPlan,o=E.a.useContainer(),c=h.a.useContainer().show,d=Object(u.useCallback)(function(){window.location=i(t)},[t]);return s.a.createElement(Ze,{disabled:l},s.a.createElement(Je,null,s.a.createElement($e,null,s.a.createElement(Qe,null,n.name),s.a.createElement(Ue,{planId:t,meal:n})),s.a.createElement(He,null,s.a.createElement("span",null,n.ideal_totals.kcal," kcals"),a===y&&s.a.createElement(I.a,{value:n.macroSplit,options:tn,onSelect:function(e,t){e.preventDefault(),window.confirm("Are you sure you wish to change macro split for this meal?")&&o.dispatch(v.a.MEAL_MACRO_SPLIT,{meal:n,value:t.value})},renderTrigger:function(){var e=tn.findIndex(function(e){return e.value===n.macroSplit});return e<0&&(e=0),s.a.createElement(A.l,{href:"#",style:{zIndex:20}},tn[e].label)}}))),s.a.createElement(T.c,{droppableId:n.id},function(e,a){return s.a.createElement(en,Object.assign({loading:l,ref:e.innerRef,isDraggingOver:a.isDraggingOver},e.droppableProps),n.meals.map(function(e,a){return s.a.createElement(R,{dish:e,index:a,mealId:n.id,planId:t,onPlanView:d,key:"dish_".concat(e.id),onShowRecipes:c.bind(null,O.a.RECIPES,{meal:n,dish:e}),onRemove:r.bind(null,n.id,a,!0)})}),e.placeholder)}),s.a.createElement(Xe,null,s.a.createElement(A.c,{type:"button",onClick:c.bind(null,O.a.RECIPES,{meal:n})},s.a.createElement(D.a,null),s.a.createElement("span",null,"Add alternative"))))}),rn=t(6),ln=t.n(rn),on=t(14),cn=[{value:"1",label:"Breakfast"},{value:"2",label:"Lunch"},{value:"3",label:"Dinner"},{value:"4",label:"Morning snack"},{value:"5",label:"Afternoon snack"},{value:"6",label:"Evening snack"},{value:"0",label:"Other"}],un=t(108),sn=t(18),dn=Object(z.b)(Le.b)(we||(we=Object(c.a)([""]))),mn=(Object(z.b)(Le.d)(Ce||(Ce=Object(c.a)([""]))),Object(z.b)(Le.c)(Se||(Se=Object(c.a)(["\n  display: flex;\n  flex-direction: column;\n"]))),z.b.div(Te||(Te=Object(c.a)(["\n  display: flex;\n  width: 100%;\n  justify-content: flex-end;\n  >button {\n    margin-left: 5px;\n    margin-right: 5px;\n    &:first-of-type {\n      margin-left: 0;\n    }\n    &:last-of-type {\n      margin-right: 0;\n    }\n  }\n"])))),pn={type:0,name:"Extra"},bn=Object(u.memo)(function(e){var n=e.onSubmit,t=e.onCancel,a=(e.mealTypesOptions,Object(u.useState)(null)),r=Object(L.a)(a,2),l=r[0],i=r[1],o=Object(u.useCallback)(function(){var e=Object(on.a)(ln.a.mark(function e(t,a){return ln.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return i(null),e.prev=1,e.next=4,n(t);case 4:e.next=9;break;case 6:e.prev=6,e.t0=e.catch(1),i(e.t0.message);case 9:return e.prev=9,a.setSubmitting(!1),e.finish(9);case 12:case"end":return e.stop()}},e,null,[[1,6,9,12]])}));return function(n,t){return e.apply(this,arguments)}}(),[]);return s.a.createElement(un.a,{initialValues:pn,onSubmit:o},function(e){e.values,e.errors,e.touched,e.handleChange,e.handleBlur;var n=e.handleSubmit,a=e.isSubmitting;return s.a.createElement(dn,{onSubmit:n},s.a.createElement("p",null,"This will add an extra meal to this meal plan, and will update the ingredients in the existing meals."),l&&s.a.createElement(A.a,{type:"danger",multiline:!0},l),s.a.createElement(mn,null,s.a.createElement(A.c,{disabled:a,type:"button",onClick:t},"Cancel"),s.a.createElement(A.c,{disabled:a,modifier:"primary",type:"submit"},a?"Adding...":"Add")))})}),fn=z.b.div(Pe||(Pe=Object(c.a)(["\n    display: flex;\n    flex-direction: column;\n    justify-content: center;\n    align-items: center;\n    border: 1px solid rgb(242, 246, 250);\n    position: absolute;\n    top: 50%;\n    transform: translateY(-50%);\n    right: -10px;\n    @media (max-width: 992px) {\n      top: initial;\n      right: initial;\n      left: 50%;\n      transform: translateX(-50%);\n      bottom: -10px;\n    }\n"]))),gn=Object(z.b)(A.c)(De||(De=Object(c.a)(["\n    padding: 10px;\n"]))),En=function(e){var n=e.onSubmit,t=e.onCancel,a=e.mealTypeOptions;return s.a.createElement(u.Fragment,null,s.a.createElement(sn.b,null,s.a.createElement(bn,{onSubmit:n,onCancel:t,mealTypesOptions:a})))},hn=function(e){var n=e.planId,t=P.a.useContainer(),a=t.mealsByPlan,r=t.addParent,l=h.a.useContainer(),i=l.show,o=l.hide,c=l.loading,d=a(n),m=Object(u.useMemo)(function(){var e=d.map(function(e){return Number(e.type)}),n=cn.filter(function(n){return!e.includes(Number(n.value))});return[{value:"",label:"Select type"}].concat(Object(Y.a)(n))},[d]),p={onSubmit:Object(u.useCallback)(function(){var e=Object(on.a)(ln.a.mark(function e(t){return ln.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,r(n,t,!0);case 2:o();case 3:case"end":return e.stop()}},e)}));return function(n){return e.apply(this,arguments)}}(),[n,r]),onCancel:Object(u.useCallback)(function(){o()},[]),mealTypeOptions:m};return d.length<6&&m.some(function(e){return""!==e.value})?s.a.createElement(fn,null,s.a.createElement(gn,{onClick:function(){c(!1),i(O.a.CUSTOM,{content:function(){return s.a.createElement(En,p)},title:"Add extra meal"})}},s.a.createElement(D.a,null))):null},vn=s.a.memo(function(e){var n=e.planId,t=e.planType,a=P.a.useContainer(),r=a.mealsByPlan,l=a.onDishDragEnd,i=a.onRemoveDish,o=a.isMealSyncing,c=a.viewMealPlan,u=r(n);return s.a.createElement(nn,null,s.a.createElement(T.a,{onDragEnd:l},u.map(function(e){return s.a.createElement(an,{id:e.id,meal:e,planType:t,planId:n,viewMealPlan:c,onRemoveDish:i,isLoading:o(e.id),key:"meal_".concat(e.id)})}),t===y&&s.a.createElement(hn,{planId:n})))}),xn=t(73),On=z.b.div(_e||(_e=Object(c.a)(["\n  display: flex;\n  align-items: center;\n  border-bottom: 1px solid #e6ebf1;\n  flex-wrap: wrap;\n  position: relative;\n  z-index: 20;\n\n  ","\n"])),We.a.desktop(Me||(Me=Object(c.a)(["\n    flex-wrap: nowrap;\n  "])))),jn=z.b.div(Ae||(Ae=Object(c.a)(["\n  font-size: 13px;\n  padding: 8px 16px;\n  position: relative;\n  text-align: left;\n  flex: ",";\n  width: ",'\n\n  & + &::before {\n    content: "";\n    position: absolute;\n    top: 50%;\n    left: 0;\n    width: 1px;\n    height: 24px;\n    background-color: #e6ebf1;\n    transform: translateY(-50%);\n  }\n\n  '," {\n    margin-right: 4px;\n  }\n\n  ","\n  \n  "," {\n    > * + * {\n      margin-left: 6px; \n    }\n  }\n"])),function(e){return e.fill?"1 1 100%":"0 0 auto"},function(e){return e.fill?"100%":"50%"},A.i,We.a.desktop(Re||(Re=Object(c.a)(["\n    padding: 16px;\n    width: auto;\n    flex: ",";\n  "])),function(e){return e.fill?"1 1 auto":"0 0 auto"}),A.f),yn=t(25),kn=t(165),wn=t.n(kn),Cn=t(467),Sn=z.b.div(Ie||(Ie=Object(c.a)(["\n  width: 232px;\n  padding: 0 5px;\n\n  "," {\n    margin-bottom: 16px;\n  }\n"])),Le.c),Tn=z.b.div(Ye||(Ye=Object(c.a)(["\n  text-align: right;\n"]))),Pn=Object(u.memo)(function(e){var n=e.plan,t=e.type,a=e.title,r=e.position,l=Object(u.useRef)(),i=Object(u.useRef)(),o=Object(u.useState)(!0),c=Object(L.a)(o,2),d=c[0],m=c[1],p=Object(u.useState)(!1),b=Object(L.a)(p,2),f=b[0],g=b[1],E=Object(u.useState)(null),h=Object(L.a)(E,2),v=h[0],x=h[1],O=P.a.useContainer().updateMealPlan,j=Object(u.useCallback)(function(){i.current&&q()(i.current.close,50)},[]),y=Object(u.useCallback)(function(){var e=Object(on.a)(ln.a.mark(function e(a,r){var l,o,c;return ln.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:if(m(!1),x(null),l=!1,o=!0,(t===Pn.TYPE_KCALS&&n.desired_kcals!==a.kcals||t===Pn.TYPE_MACROS&&n.avg_totals!==a.totals)&&(window.confirm("This will update all meals and erase any changes you may have made")||(o=!1),l=!0),!o){e.next=22;break}return t===Pn.TYPE_MACROS&&(a={macros:a},f&&(a.approved=!0)),e.prev=7,e.next=10,O(n.id,a,l);case 10:g(!1),q()(i.current.close,50),e.next=18;break;case 14:e.prev=14,e.t0=e.catch(7),(c=wn()(e.t0.response.data,"errors",0))>0&&(g(!0),x("".concat(c," recipes could not be generated, do you wish to go ahead anyway?")));case 18:return e.prev=18,m(!0),r.setSubmitting(!1),e.finish(18);case 22:case"end":return e.stop()}},e,null,[[7,14,18,22]])}));return function(n,t){return e.apply(this,arguments)}}(),[n,t,f]),k=Object(u.useCallback)(function(){f&&l.current.setValues(S),x(""),g(!1)},[S,f]),w=Object(u.useCallback)(function(e){var n,a=(n={},Object(F.a)(n,Pn.TYPE_NAME,{name:"required"}),Object(F.a)(n,Pn.TYPE_KCALS,{kcals:"required|number"}),Object(F.a)(n,Pn.TYPE_MACROS,{carbohydrate:"required",protein:"required",fat:"required"}),Object(F.a)(n,Pn.TYPE_STATUS,{active:"boolean"}),n)[t];return Object(Cn.validateAll)(e,a||{}).then(function(){}).catch(function(e){return e.reduce(function(e,n){return e[n.field]=n.message,e},{})}).then(function(e){if(e)throw e})},[n,t]),C=Object(u.useCallback)(function(e){return e===t},[t]),S=Object(u.useMemo)(function(){var e;return(e={},Object(F.a)(e,Pn.TYPE_NAME,{name:n.name}),Object(F.a)(e,Pn.TYPE_KCALS,{kcals:n.desired_kcals}),Object(F.a)(e,Pn.TYPE_MACROS,{carbohydrate:n.avg_totals.carbohydrate,protein:n.avg_totals.protein,fat:n.avg_totals.fat}),Object(F.a)(e,Pn.TYPE_STATUS,{active:n.active}),Object(F.a)(e,Pn.TYPE_DURATION,{duration:n.meta&&n.meta.duration?n.meta.duration:4}),e)[t]||{}},[n,t]),T=Object(u.useCallback)(function(){return s.a.createElement(A.l,{href:"#"},a)},[a]),D=Object(u.useCallback)(function(){return s.a.createElement(un.a,{initialValues:S,validate:w,onSubmit:y,ref:l},function(e){var n=e.values,t=e.errors,a=e.touched,r=e.handleChange,l=e.handleBlur,i=e.handleSubmit,o=e.isSubmitting;return s.a.createElement("form",{onSubmit:i},s.a.createElement(Sn,null,C(Pn.TYPE_NAME)&&s.a.createElement(Le.c,null,s.a.createElement(Le.d,{required:!0},"Title of meal plan"),s.a.createElement(Le.e,{name:"name",value:n.name,type:"text",onChange:r,onBlur:l}),t.name&&a.name?s.a.createElement(Le.a,{type:"invalid"},t.name):null),C(Pn.TYPE_KCALS)&&s.a.createElement(Le.c,null,s.a.createElement(Le.d,{required:!0},"Target",s.a.createElement(A.b,null,"Kcals")),s.a.createElement(Le.e,{name:"kcals",type:"number",min:0,step:1,value:n.kcals,onChange:r,onBlur:l}),t.desired_kcals&&a.desired_kcals?s.a.createElement(Le.a,{type:"invalid"},t.desired_kcals):null),C(Pn.TYPE_DURATION)&&s.a.createElement(Le.c,null,s.a.createElement(Le.d,{required:!0},"Duration of meal plan"),s.a.createElement(Le.f,{name:"duration",value:n.duration,onChange:r,onBlur:l},[4,6,8,12,16].map(function(e){return s.a.createElement("option",{value:e,key:"week_".concat(e)},e)})),t.name&&a.name?s.a.createElement(Le.a,{type:"invalid"},t.name):null),C(Pn.TYPE_MACROS)&&s.a.createElement(Le.c,null,s.a.createElement(Le.d,{required:!0},s.a.createElement(A.b,null,"Carbs")),s.a.createElement(Le.e,{name:"carbohydrate",type:"number",value:n.carbohydrate,onChange:r,onBlur:l}),s.a.createElement(Le.d,{required:!0},s.a.createElement(A.b,null,"Protein")),s.a.createElement(Le.e,{name:"protein",type:"number",value:n.protein,onChange:r,onBlur:l}),s.a.createElement(Le.d,{required:!0},s.a.createElement(A.b,null,"Fat")),s.a.createElement(Le.e,{name:"fat",type:"number",value:n.fat,onChange:r,onBlur:l})),C(Pn.TYPE_STATUS)&&s.a.createElement(Le.c,null,s.a.createElement(Le.g,{single:!0,name:"active",label:"Plan visible",checked:n.active,value:!0,onChange:r,onBlur:l})),v&&s.a.createElement(A.a,{type:"danger",multiline:!0},v),s.a.createElement(Tn,null,s.a.createElement(A.e,{type:"button",onClick:j},"Cancel"),s.a.createElement(A.c,{modifier:f?"danger":"primary",disabled:o,onClick:i,type:"button"},o?"Saving...":f?"Yes, approve it!":"Save"))))})},[n,v,f]);return s.a.createElement(I.a,{ref:i,canClose:d,onClose:k,renderTrigger:T,renderBody:D,position:r})});Pn.TYPE_NAME="name",Pn.TYPE_KCALS="kcals",Pn.TYPE_STATUS="status",Pn.TYPE_MACROS="macros",Pn.TYPE_DURATION="duration",Pn.defaultProps={title:"Edit"};var Dn,_n,Mn,An=Pn,Rn=t(72),In=[{label:function(e){return s.a.createElement(s.a.Fragment,null,s.a.createElement(A.k,null,"Target Kcals"),e.type===y&&s.a.createElement(An,{plan:e,type:An.TYPE_KCALS,position:"left"}))},value:function(e){return e.desired_kcals}},{label:function(e){var n=window.innerWidth<=540?"right":"left";return s.a.createElement(s.a.Fragment,null,s.a.createElement(A.k,null,"Macros"),e.type===k&&s.a.createElement(An,{plan:e,type:An.TYPE_MACROS,position:n}))},value:function(e){if(e.type===y)return x[e.macro_split];var n=e.avg_totals,t=n.carbohydrate,a=n.protein,r=n.fat,l=Math.round(4*t/(4*t+4*a+9*r)*100),i=Math.round(4*a/(4*t+4*a+9*r)*100),o=Math.round(9*r/(4*t+4*a+9*r)*100);return"C".concat(t," (").concat(l,"%), P").concat(a," (").concat(i,"%), F").concat(r," (").concat(o,"%)")}},{label:function(e){return s.a.createElement(s.a.Fragment,null,s.a.createElement(A.k,null,"Status"),s.a.createElement(An,{plan:e,type:An.TYPE_STATUS,position:"left"}))},value:function(e){return e.active?s.a.createElement(s.a.Fragment,null,s.a.createElement(A.i,{type:"success"},s.a.createElement(p.a,null)),"Plan is visible"):s.a.createElement(s.a.Fragment,null,s.a.createElement(A.i,{type:"danger"},s.a.createElement(b.a,null)),"Plan is hidden")}},{label:function(){return s.a.createElement(A.k,null,"Created")},value:function(e){return m.DateTime.fromSQL(e.created).toFormat("DD")}},{label:function(e){return s.a.createElement(s.a.Fragment,null,s.a.createElement(A.k,null,"Duration"),s.a.createElement(An,{plan:e,type:An.TYPE_DURATION,position:"left"}))},value:function(e){return e.meta&&e.meta.duration?"".concat(e.meta.duration," weeks"):"N/A"}}],Yn=[{value:j.a.PDF,label:"Save as PDF"},{divider:!0},{value:j.a.DELETE,label:"Delete",modifier:"danger"}],Fn=function(e,n,t){return function(a,r){switch(a.preventDefault(),r.value){case j.a.DELETE:e.show(O.a.SETTINGS,{plan:t,settingsType:r.value});break;case j.a.PDF:n.dispatch(v.a.PDF_DOWNLOAD,{plan:t})}}},Ln=function(){return s.a.createElement(A.e,{type:"button",icon:!0},s.a.createElement(g.a,null))},zn=Object(z.b)(xn.a)(Dn||(Dn=Object(c.a)(["\n  position: relative;\n"]))),Nn=z.b.div(_n||(_n=Object(c.a)(["\n  filter: ",";\n"])),function(e){return e.loading?"blur(1px)":"none"}),Bn=z.b.div(Mn||(Mn=Object(c.a)(["\n  position: absolute;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  z-index: 100;\n  filter: blur(0px);\n  display: flex;\n  justify-content: center;\n  align-items: center;\n"]))),Vn=s.a.memo(function(e){var n=e.plan,t=e.open,a=e.onView,r=e.onViewMeals,l=E.a.useContainer(),i=h.a.useContainer(),o=Object(u.useCallback)(function(){window.location=a(n.id)},[n.id]),c=Object(u.useCallback)(function(e){e&&e.preventDefault(),i.show(O.a.SETTINGS,{plan:n,settingsType:j.a.SETTINGS_DESCRIPTION})},[n.id]),m=!!n.explaination,b=function(e,n){return e-n};return s.a.createElement(zn,null,n.loading&&s.a.createElement(Bn,null,s.a.createElement(Rn.a,{size:10})),s.a.createElement(Nn,{loading:n.loading},s.a.createElement(xn.f,null,s.a.createElement("div",{style:{position:"absolute",width:"100%",height:"100%",zIndex:-1,cursor:"pointer"},onClick:r}),s.a.createElement(xn.g,null,s.a.createElement("span",null,n.name),s.a.createElement(An,{plan:n,type:An.TYPE_NAME,position:"left"})),s.a.createElement("div",{style:{flex:1}}),l.current&&s.a.createElement(S,null),s.a.createElement(I.a,{options:Yn,onSelect:Fn(i,l,n),renderTrigger:Ln}),s.a.createElement(A.c,{type:"button",onClick:o},s.a.createElement(f.a,null),s.a.createElement("span",null,"Edit"))),t&&s.a.createElement(d.a,{in:t},s.a.createElement(u.Fragment,null,s.a.createElement(On,null,In.map(function(e,t){return s.a.createElement(jn,{key:"col_".concat(t),style:{zIndex:b(5,t)}},s.a.createElement(A.f,null,Object(yn.b)(e.label)?e.label(n):e.label),s.a.createElement("span",null,e.value(n)))}),s.a.createElement(jn,{style:{zIndex:b(5,5)}},s.a.createElement(A.k,null,"Description"),s.a.createElement(A.l,{href:"#",onClick:c},m?s.a.createElement(s.a.Fragment,null,s.a.createElement(A.i,{type:"success"},s.a.createElement(p.a,null)),"View Description"):"Create Description")),null),s.a.createElement(xn.e,null,s.a.createElement(vn,{planType:n.type,planId:n.id}))))))});n.default=Vn}}]);
//# sourceMappingURL=9.90050d81.chunk.js.map