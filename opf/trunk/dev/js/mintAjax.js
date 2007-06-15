/*
Copyright 2007 Piotr Korzeniewski www.mintAjax.pl

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
*/

// mintAjax version 1.0.1
 
var mint =
{
	Request : function()
	{
		var newRequestObject =
		{
			xmlHttpRequest : null,

			responseText : null,
			responseXML : null,
			responseJSON : null,
			getJSON : false,

			params : new Array(),

			url : "",
			async : true,
			method : "GET",
			contentType : "text/plain",
			username : "",
			password : "",

			form : null,
			disableForm : true,
			
			status : null,
			statusText : null,

			reqDone : false,
			retryCount : 0,
			retryNum : 3,
			timeout : 5000,

			OnStateChange : function() {},
			OnLoading : function() {},
			OnLoaded : function() {},
			OnInteractive : function() {},
			OnComplete : function() {},
			OnSuccess : function() {},
			OnError : function() {},
			OnAbort : function() {},
			OnRetry : function() {},
			OnTimeout : function() {},

			Send : function(url, target)
			{
				var paramStr = "";

				this.reqDone = false;

				!url ? url = this.url : this.url = url;

				if(window.XMLHttpRequest)
					this.xmlHttpRequest = new XMLHttpRequest();
				else if(window.ActiveXObject)
				{
					try	{
						this.xmlHttpRequest = new ActiveXObject("Msxml2.XMLHTTP");
					}
					catch(e) {
						this.xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
					}
				}

				for(var i in this.params)
				{
					if(i != 0)
						paramStr += "&";

					paramStr += this.params[i].name+"="+this.params[i].value;
				}

				if(this.method == "post")
					this.xmlHttpRequest.open(this.method, url, this.async, this.username, this.password);
				else
					this.xmlHttpRequest.open(this.method, url+(!/\?/.test(url) ? "?"+paramStr : "&;"+paramStr), this.async, this.username, this.password);
					
				try {
				if(this.method == "post")
					this.xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				else
					this.xmlHttpRequest.setRequestHeader("Content-Type", this.contentType);
				} catch(e) {}
					
				try {
					this.xmlHttpRequest.setRequestHeader("If-Modified-Since", "Sat, 11 Jan 1977 00:00:00 GMT");
				} catch(e) {}

				var that = this;

				this.xmlHttpRequest.onreadystatechange =
				function()
				{
					that.OnStateChange();

					switch(that.xmlHttpRequest.readyState)
					{
						case 1:
							that.OnLoading();
							break;
						case 2:
							that.OnLoaded();
							break;
						case 3:
							that.OnInteractive();
							break;
						case 4:
							that.OnComplete();

							if(that.xmlHttpRequest.status == 200)
							{
								that.reqDone = true;

								that.responseText = that.xmlHttpRequest.responseText;
								that.responseXML = that.xmlHttpRequest.responseXML;
								
								that.status = that.xmlHttpRequest.status;
								that.statusText = that.xmlHttpRequest.statusText;
								
								if(target)
									$(target).innerHTML = that.responseText;

								if(that.getJSON)
									that.responseJSON = eval('(' + that.responseText + ')');

								if(that.form && that.disableForm)
								{
									for(var i = 0; i < that.form.elements.length; i++)
									{
										that.form.elements[i].disabled = false;
									}
								}

								that.OnSuccess();
							}
							else
								that.OnError(that.xmlHttpRequest.status);

							break;
					}
				}
					
				if(this.method == "post")
					this.xmlHttpRequest.send(paramStr);
				else
					this.xmlHttpRequest.send(null);

				setTimeout(
						function()
						{
			    			if(!that.reqDone)
							{
								that.xmlHttpRequest.onreadystatechange = function() {};
								that.xmlHttpRequest.abort();
								that.OnTimeout();

								if(that.retryCount < that.retryNum)
								{
									that.retryCount++;
									that.Send();
									that.OnRetry();
								}
								else
								{
									that.retryCount = 0;
									that.OnAbort();
								}
							}
						},
						this.timeout);

				this.params.length = 0;
			},

			SendForm : function(form, url, method)
			{
				this.form = $(form);

				method ? this.method = method : this.method = this.form.method

				if(!url) url = this.form.action;

				var input = this.form.elements;

				for(var i = 0; i < input.length; i++)
				{
					if(this.disableForm)
						input[i].disabled = true;

					switch(input[i].type)
					{
						case "radio":
						case "checkbox":
							if(input[i].checked)
								this.AddParam(input[i].name, input[i].value);
							break;
						case "select-one":
							this.AddParam(input[i].name, input[i].options[input[i].selectedIndex].value);
							break;
						case "select-multiple":
							for(var x = 0; x < input[i].options.length; x++)
							{
								if(input[i].options[x].selected)
									this.AddParam(input[i].name, input[i].options[x].value);
							}
							break;
						default:
							this.AddParam(input[i].name, input[i].value);
					}
				}

				this.Send(url);
			},

			AddParam : function(name, value)
			{
				var newParam =
				{
					name : name,
					value : value
				}

				this.params.push(newParam);
			}
		}

		return newRequestObject;
	},

	fx :
	{
		Stop : function(obj, fxType)
		{
			obj = $(obj);
			
			if(!fxType)
			{
				
				if(obj.fxSizeTimeoutID)
				{
					clearTimeout(obj.fxSizeTimeoutID);
					obj.fxSizeTimeoutID = null;
				}
				
				if(obj.fxMoveTimeoutID)
				{
					clearTimeout(obj.fxMoveTimeoutID);
					obj.fxMoveTimeoutID = null;
				}
				
				if(obj.fxFadeTimeoutID)
				{
					clearTimeout(obj.fxFadeTimeoutID);
					obj.fxFadeTimeoutID = null;
				}
				
				for(var i in obj)
				{
					if(i.search(/fxColorTimeoutID/) != -1 && obj[i])
					{
						clearTimeout(obj[i]);
						obj[i] = null;
					}
				}
			}
			else
			{
				switch(fxType)
				{
					case "size":
					{
						clearTimeout(obj.fxSizeTimeoutID);
						obj.fxSizeTimeoutID = null;
						break;
					}
					case "move":
					{
						clearTimeout(obj.fxMoveTimeoutID);
						obj.fxMoveTimeoutID = null;
						break;
					}
					case "fade":
					{
						clearTimeout(obj.fxFadeTimeoutID);
						obj.fxFadeTimeoutID = null;
						break;
					}
					case "color":
					{
						for(var i in obj)
						{
							if(i.search(/fxColorTimeoutID/))
							{
								clearTimeout(obj[i]);
								obj[i] = null;
							}
						}
						break;
					}
				}
			}
		},

		Size : function(obj, width, height, steps, duration, OnSize, OnSizeDone)
		{
			obj = $(obj);

			if(obj.id.length == 0)
				obj.id = GenerateID();

			if(obj.fxSizeTimeoutID)
			{
				clearTimeout(obj.fxSizeTimeoutID);
				obj.fxSizeTimeoutID = null;
			}

			if(width == null) width = GetWidth(obj);
			if(height == null) height = GetHeight(obj);

			this._Size(obj, GetWidth(obj), GetHeight(obj), width, height, (width-GetWidth(obj))/steps,  (height-GetHeight(obj))/steps, duration/steps, OnSize, OnSizeDone);
		},

		_Size : function(obj, width, height, endWidth, endHeight, stepWidth, stepHeight, stepTime, OnSize, OnSizeDone)
		{
			obj = $(obj);

			if(IsF(OnSize)) OnSize(obj);

			width += stepWidth;
			height += stepHeight;

			if((stepWidth < 0 && width < endWidth) || (stepWidth > 0 && width > endWidth))
				width = endWidth;

			if((stepHeight < 0 && height < endHeight) || (stepHeight > 0 && height > endHeight))
				height = endHeight;

			SetSize(obj, parseInt(width), parseInt(height));

			if(parseInt(width) != endWidth || parseInt(height) != endHeight)
				obj.fxSizeTimeoutID = setTimeout("mint.fx._Size('"+obj.id+"', "+width+", "+height+", "+endWidth+", "+endHeight+", "+stepWidth+", "+stepHeight+", "+stepTime+", "+OnSize+", "+OnSizeDone+")", stepTime);
			else
			{
				if(IsF(OnSizeDone)) OnSizeDone(obj);
				obj.fxSizeTimeoutID = null;
			}

		},

		Move : function(obj, x, y, steps, duration, OnMove, OnMoveDone)
		{
			obj = $(obj);

			if(obj.id.length == 0)
				obj.id = GenerateID();

			if(obj.fxMoveTimeoutID)
			{
				clearTimeout(obj.fxMoveTimeoutID);
				obj.fxMoveTimeoutID = null;
			}

			obj.style.margin = "0px";
			obj.style.padding = "0px";
			
			if(x == null) x = GetX(obj);
			if(y == null) y = GetY(obj);

			this._Move(obj, GetX(obj), GetY(obj), x, y, (x-GetX(obj))/steps, (y-GetY(obj))/steps, duration/steps, OnMove, OnMoveDone);
		},

		_Move : function(obj, x, y, endX, endY, stepX, stepY, stepTime, OnMove, OnMoveDone)
		{
			obj = $(obj);

			if(IsF(OnMove)) OnMove(obj);

			x += stepX;
			y += stepY;

			if((stepX < 0 && x < endX) || (stepX > 0 && x > endX))
				x = endX;

			if((stepY < 0 && y < endY) || (stepY > 0 && y > endY))
				y = endY;

			SetPos(obj, parseInt(x), parseInt(y));

			if(parseInt(x) != endX || parseInt(y) != endY)
				obj.fxMoveTimeoutID = setTimeout("mint.fx._Move('"+obj.id+"', "+x+", "+y+", "+endX+", "+endY+", "+stepX+", "+stepY+", "+stepTime+", "+OnMove+", "+OnMoveDone+")", stepTime);
			else
			{
				if(IsF(OnMoveDone)) OnMoveDone(obj);
				obj.fxMoveTimeoutID = null;
			}
		},

		Fade : function(obj, endOpacity, steps, duration, OnFade, OnFadeDone)
		{
			obj = $(obj);

			if(obj.id.length == 0)
				obj.id = GenerateID();

			if(obj.fxFadeTimeoutID)
			{
				clearTimeout(obj.fxFadeTimeoutID);
				obj.fxFadeTimeoutID = null;
			}

			this._Fade(obj, GetOpacity(obj), endOpacity, (endOpacity-GetOpacity(obj))/steps, parseInt(duration/steps), OnFade, OnFadeDone);
		},

		_Fade : function(obj, opacity, endOpacity, step, stepTime, OnFade, OnFadeDone)
		{
			obj = $(obj);
			
			if(IsF(OnFade)) OnFade(obj);
			
			opacity += step;

			if((step > 0 && opacity > endOpacity) || (step < 0 && opacity < endOpacity))
				opacity = endOpacity;

			SetOpacity(obj, parseInt(opacity));

			if(parseInt(opacity) != endOpacity)
				obj.fxFadeTimeoutID = setTimeout("mint.fx._Fade('"+obj.id+"', "+opacity+", "+endOpacity+", "+step+", "+stepTime+", "+OnFade+", "+OnFadeDone+")", stepTime);
			else
			{
				if(IsF(OnFadeDone)) OnFadeDone(obj);
				obj.fxFadeTimeoutID = null;
			}
		},

		Color : function(obj, style, startColor, endColor, steps, duration, OnColor, OnColorDone)
		{
			obj = $(obj);

			if(obj.id.length == 0)
				obj.id = GenerateID();

			if(obj[style+"fxColorTimeoutID"])
			{
				clearTimeout(obj[style+"fxColorTimeoutID"]);
				obj[style+"fxColorTimeoutID"] = null;
			}

			if(!startColor)
			{
				var styleCss = new Array();

				for(var i in style)
				{
					if(/[A-Z]/.test(style[i]))
						styleCss.push('-');

					styleCss.push(style[i].toLowerCase());
				}

				styleCss = styleCss.toString().replace(/,/g, "");

				if(obj.style[style].length == 0)
				{
					if(obj.currentStyle)
						obj.style[style] = obj.currentStyle[style];
					else if(window.getComputedStyle)
						obj.style[style] = getComputedStyle(obj, "").getPropertyValue(styleCss);
				}

				if(/^rgb\( ?(\d{1,3}), ?(\d{1,3}), ?(\d{1,3})\)$/.test(obj.style[style]))
					startColor =  { r : parseInt(RegExp.$1), g : parseInt(RegExp.$2), b : parseInt(RegExp.$3) }
				else
					startColor = HexToRGB(obj.style[style]);
			}
			else
				startColor = HexToRGB(startColor);

			endHexColor = endColor;
			endColor = HexToRGB(endColor);

			this._Color(obj, style, endHexColor, startColor.r, startColor.g, startColor.b, endColor.r, endColor.g, endColor.b, (endColor.r-startColor.r)/steps, (endColor.g-startColor.g)/steps, (endColor.b-startColor.b)/steps, duration/steps, OnColor, OnColorDone);
		},

		_Color : function(obj, style, endColor, r, g, b, endR, endG, endB, stepR, stepG, stepB, stepTime, OnColor, OnColorDone)
		{
			obj = $(obj);

			if(IsF(OnColor)) OnColor(obj);

			r += stepR;
			g += stepG;
			b += stepB;

			if((stepR < 0 && r < endR) || (stepR > 0 && r > endR))
				r = endR;
			if((stepG < 0 && g < endG) || (stepG > 0 && g > endG))
				g = endG;
			if((stepB < 0 && b < endB) || (stepB > 0 && b > endB))
				b = endB;

			obj.style[style] = "rgb("+parseInt(r)+", "+parseInt(g)+", "+parseInt(b)+")";

			if(parseInt(r) != endR || parseInt(g) != endG || parseInt(b) != endB)
				obj[style+"fxColorTimeoutID"] = setTimeout("mint.fx._Color('"+obj.id+"', '"+style+"', '"+endColor+"', "+r+", "+g+", "+b+", "+endR+", "+endG+", "+endB+", "+stepR+", "+stepG+", "+stepB+", "+stepTime+", "+OnColor+", "+OnColorDone+")", stepTime);
			else
			{
				if(IsF(OnColorDone)) OnColorDone(obj);
	   			obj[style+"fxColorTimeoutID"] = null;
				obj.style[style] = endColor;
			}
		}
	},
	
	gui :
	{
		dragObject : null,
		dragStartX : 0,
		dragStartY : 0,
		dragOffsetX : 0,
		dragOffsetY : 0,
	
		stack : new Array(),
	
		tabWidgets : new Array(),
		treeWidgets : new Array(),
		gridWidgets : new Array(),
		dragObjects : new Array(),
		dropZones : new Array(),
	
		Init : function()
		{
			var htmlTag = document.getElementsByTagName("html")[0];
	
			var that = this;
	
			AddEvent(htmlTag, "mousemove", function(event) {that.OnMouseMove(event)});
			AddEvent(htmlTag, "mouseup", function(event) {that.OnMouseUp(event)});
		},
	
		OnMouseMove : function(event)
		{
			if(this.dragObject)
			{
				if(window.getSelection)
					window.getSelection().removeAllRanges();
				else if(document.getSelection)
					document.getSelection().removeAllRanges();
				else if(document.selection)
					document.selection.empty();
	
				var dragObject = this.dragObject, dropZone;
				
				if(	!dragObject.isDragged && dragObject.threshold != 0 && 
					Math.pow(event.clientX-this.dragStartX, 2)+Math.pow(event.clientY-this.dragStartY, 2) > Math.pow(this.dragObject.threshold, 2))
				{
					var pos = GetPos(dragObject.obj);
					SetPos(dragObject.obj, pos.x, pos.y);
					dragObject.obj.style.position = "absolute";	
					
					dragObject.OnDragStart(dragObject.obj);
					dragObject.isDragged = true;
				}
	
				if(!dragObject.lockX)
				{
					if(dragObject.minX && event.clientX-this.dragOffsetX < dragObject.minX)
						SetX(dragObject.obj, dragObject.minX);
					else if(dragObject.maxX && event.clientX-this.dragOffsetX+GetWidth(dragObject.obj) > dragObject.maxX)
						SetX(dragObject.obj, dragObject.maxX-GetWidth(dragObject.obj));
					else
						SetX(dragObject.obj, event.clientX-this.dragOffsetX);
				}
	
				if(!dragObject.lockY)
				{
					if(dragObject.minY && event.clientY-this.dragOffsetY < dragObject.minY)
						SetY(dragObject.obj, dragObject.minY);
					else if(dragObject.maxY && event.clientY-this.dragOffsetY+GetHeight(dragObject.obj) > dragObject.maxY)
						SetY(dragObject.obj, dragObject.maxY-GetHeight(dragObject.obj));
					else
						SetY(dragObject.obj, event.clientY-this.dragOffsetY);
				}
	
				for(var i in this.dropZones)
				{
					dropZone = this.dropZones[i];
	
					if(IsInside(dropZone.obj, event.clientX, event.clientY))
					{
						if((dropZone.acceptClass && dragObject.defaultClass == dropZone.acceptClass) ||
							(!dropZone.acceptClass && dropZone.OnAccept(dragObject.obj)))
						{
							if(!dropZone.hover)
							{
								if(dropZone.hoverClass)
									dropZone.obj.className = dropZone.hoverClass;
	
								dropZone.hover = dragObject;
								dropZone.OnHoverIn(dragObject.obj);
							}
	
							if(!dropZone.over)
							{
								if(dropZone.over = GetChildAtPos(dropZone.obj, event.clientX, event.clientY))
								{
									if(dropZone.useDummyNode)
									{
										if(dropZone.dummyNode)
											dropZone.obj.removeChild(dropZone.dummyNode);
	
										dropZone.dummyNode = dragObject.obj.cloneNode(false);
										dropZone.dummyNode.style.position = "static";
	
										if(dropZone.dummyNodeClass)
											dropZone.dummyNode.className = dropZone.dummyNodeClass;
										else
											dropZone.dummyNode.style.visibility = "hidden";
	
										dropZone.obj.insertBefore(dropZone.dummyNode, dropZone.over);
										dropZone.over = dropZone.dummyNode;
									}
									else
									{
										if(dropZone.overClass)
										{
											dropZone.defaultOverClass = dropZone.over.className;
											dropZone.over.className = dropZone.overClass;
										}
	
										dropZone.OnOverIn(dragObject.obj, dropZone.over);
									}
								}
							}
							else
							{
								if(dropZone.over != GetChildAtPos(dropZone.obj, event.clientX, event.clientY))
									dropZone._ResetOverState();
								else if(!dropZone.dummyNode)
									dropZone.OnOver(dragObject.obj, dropZone.over);
							}
							
							dropZone.OnHover(dragObject.obj);
						}
	
						break;
					}
					else if(dropZone.hover)
					{
						dropZone._ResetOverState();
						dropZone._ResetHoverState();
					}
				}
	
			}
	
		},
	
		OnMouseUp : function(event)
		{
			if(this.dragObject)
			{
				var dragObject = this.dragObject, dropZone;
	
				for(i in this.dropZones)
				{
					dropZone = this.dropZones[i];
	
					if(IsInside(dropZone.obj, event.clientX, event.clientY))
					{
						if((dropZone.acceptClass && dragObject.defaultClass == dropZone.acceptClass) ||
							(!dropZone.acceptClass && dropZone.OnAccept(dragObject.obj)))
						{
							dragObject.dropZone = dropZone;
	
							if(dropZone.over && dropZone.insertInside)
								dropZone.InsertItem(dragObject.obj, dropZone.over);
								
							else
								dropZone.InsertItem(dragObject.obj);
	
							dropZone._ResetOverState();
							dropZone._ResetHoverState();
						}
	
						break;
					}
				}
	
				dragObject.obj.className = dragObject.defaultClass;
	
				dragObject.OnDragStop(dragObject.obj);
				this.dragObject.isDragged = false;
				this.dragObject = null;
			}
		},
	
		AddToStack : function(obj)
		{
			$(obj).style.zIndex = this.stack.push($(obj))
		},
		
		RemoveFromStack : function(obj)
		{
			for(var i = $(obj).style.zIndex-1; i < this.stack.length-1; i++)
			{
				this.stack[i] = this.stack[i+1];
				this.stack[i].style.zIndex = i+1;
			}
	
			this.stack.pop();
		},
		
		MoveOnTop : function(obj)
		{
			this.RemoveFromStack(obj);
			this.AddToStack(obj);
		},
	
		_DragStart : function(event)
		{
	  		var that = mint.gui;
	
			event.cancelBubble = true;
			if(event.stopPropagation) event.stopPropagation();
			if(event.preventDefault) event.preventDefault();
	
			var pos = GetPos(this);
	
	  		that.dragObject = this.dragObject;
			that.dragStartX = event.clientX;
			that.dragStartY = event.clientY;
			that.dragOffsetX = event.clientX-pos.x;
			that.dragOffsetY = event.clientY-pos.y;
			
			if(that.dragObject.threshold != 0)
				return;
	
			if(that.dragObject.dropZone)
			{
				that.dragObject.dropZone.RemoveItem(this);
				that.OnMouseMove(event);
				that.dragObject.dropZone = null;
			}
			
			if(this.style.position != "absolute")
			{
				SetPos(this, pos.x, pos.y);
				this.style.position = "absolute";
			}
	
			if(this.parentNode != document.body)
				document.body.appendChild(this);
	
			if(that.dragObject.dragClass)
				that.dragObject.obj.className = that.dragObject.dragClass;
	
		 	if(that.dragObject.moveOnTop)
			 	that.MoveOnTop(this);
	
			that.dragObject.OnDragStart(this);
			that.isDragged = true;
		},
	
		RegisterDragObject : function(obj)
		{
			obj = $(obj);
	
			this.AddToStack(obj);
			
			AddEvent(obj, "mousedown", this._DragStart);
			AddEvent(obj, "dragstart", function() {return false;});
	
			var newDragObject =
			{
				obj : obj,
				minX : null,
				maxX : null,
				minY : null,
				maxY : null,
				lockX : false,
				lockY : false,
				dropZone : null,
				dragClass : null,
				defaultClass : obj.className,
				moveOnTop : true,
				threshold : 0,
				isDragged : false,
				OnDragStart : function() {},
				OnDragStop : function() {},
	
				SetBBox : function(obj)
				{
					if(!obj)
						obj = this.obj.parentNode;
					else
						obj = $(obj);
	
					var pos = GetPos(obj), size = GetSize(obj);
	
					this.minX = pos.x;
					this.maxX = pos.x+size.width;
					this.minY = pos.y;
					this.maxY = pos.y+size.height;
				},
	
				RemoveBBox : function()
				{
					this.minX = this.maxX = this.minY = this.maxY = 0;
				}
			}
			
			obj.dragObject = newDragObject;
			
			this.dragObjects.push(newDragObject);
	
			return newDragObject;
		},
	
		UnregisterDragObject : function(obj)
		{
			obj = $(obj);
	
			RemoveEvent(obj, "mousedown", this._DragStart);
			RemoveEvent(obj, "dragstart", function() {return false;});
	
			for(var i in this.dragObjects)
			{
				if(this.dragObjects[i] == obj.dragObject)
				{
					this.dragObjects.splice(i, 1);
					obj.dragObject = null
					return true;
				}
			}
	
			return false;
		},
	
		RegisterDropZone : function(obj)
		{
			obj = $(obj);
	
			var newDropZone =
			{
				obj : obj,
				over : null,
	   			hover : null,
	   			overClass : null,
	   			hoverClass : null,
	   			acceptClass : null,
	   			defaultOverClass : null,
	   			defaultHoverClass : obj.className,
				dummyNode : null,
				dummyNodeClass : null,
				useDummyNode : true,
				insertInside : true,
				autoInline : true,
				OnAdd : function() {},
				OnRemove : function() {},
				OnHover : function() {},
				OnHoverIn : function() {},
				OnHoverOut : function() {},
				OnOver : function() {},
				OnOverIn : function() {},
				OnOverOut : function() {},
				OnAccept : function() { return true; },
	
				InsertItem : function(obj, before)
				{
					obj = $(obj);
	
					before ? this.obj.insertBefore(obj, before) : this.obj.appendChild(obj) ;
	
					obj.dragObject.dropZone = this;
					obj.style.position = "static";
	
					if(this.autoInline)
					{
						obj.style.cssFloat = "left";
						obj.style.clear = "none";
					}
	
					this.OnAdd(obj);
				},
	
				RemoveItem : function(obj)
				{
					obj = $(obj);
	
					SetPos(obj, GetX(obj), GetY(obj));
					obj.style.position = "absolute";
	
					document.body.appendChild(obj);
					
					obj.dragObject.dropObject = null;
	
					this.OnRemove(obj);
				},
				
				_ResetOverState : function()
				{
					if(!this.over) return;
	
					if(this.dummyNode)
					{
						this.obj.removeChild(this.dummyNode);
						this.dummyNode = null;
					}
					else if(this.defaultOverClass)
					{
						this.over.className = this.defaultOverClass;
						this.defaultOverClass = null;
						
						this.OnOverOut(this.over);
					}
	
					this.over = null;
				},
	
				_ResetHoverState : function()
				{
					if(!this.hover) return;
	
					this.obj.className = this.defaultHoverClass;
	
					this.OnHoverOut(this.hover);
					this.hover = null;
				}
			}
	
			obj.dropZone = newDropZone;
		
			this.dropZones.push(newDropZone);
	
			return newDropZone;
		},
		
		UnregisterDropZone : function(obj)
		{
			obj = $(obj);
	
			for(var i in this.dropZones)
			{
				if(this.dropZones[i] == obj.dropZone)
				{
					this.dropZones.splice(i, 1);
					obj.dropZone = null;
					return true;
				}
			}
			
			return false;
	
		},
	
		CreateTabWidget : function(target)
		{
			target = $(target);
	
			var newTabWidget =
			{
				target : target,
				tabs : new Array(),
				name : null,
				link : null,
				activeTab : null,
				activeClass : null,
				defaultClass : null,
				activeImage : null,
				inactiveImage : null,
				useHash : false,
				useCache : true,
				autoTextUpdate : true,
				selectFirstTab : true,
				defaultHash : window.location.hash,
				tabWidgetParam : "tabWidget",
				tabItemParam : "tabItem",
				OnSelect : function() {},
				OnDeselect : function() {},
				OnUpdate : function() {},
				OnRetrieve : function() {},
	
				AddTab : function(obj, name, type, link)
				{
	    			obj = $(obj);
	
					var newTabItem =
					{
						obj : obj,
						img : this.activeImage || this.inactiveImage ? document.createElement("img") : null,
						name : name,
						type : type,			// text, xml, json, content
						link : link ? link : null,
						content : null,
						cache : null
					}
	
					obj.tabItem = newTabItem;
					obj.tabWidget = this;
	
					if(this.inactiveImage)
					{
						newTabItem.img.src = this.inactiveImage;
						newTabItem.obj.insertBefore(newTabItem.img, newTabItem.obj.firstChild);
					}
	

					newTabItem.obj.onmousedown = function() {return false};
					newTabItem.obj.onselectstart = function() {return false};
	
					AddEvent(newTabItem.obj, "click", this.__SelectTab);
	
					if((this.tabs.length == 0 && this.selectFirstTab) || (this.useHash && this.defaultHash == "#"+newTabItem.name))
						this.Select(newTabItem);
	
					this.tabs.push(newTabItem);
	
					return newTabItem;
				},
				
				__SelectTab : function(event)
				{
	    			this.tabWidget.Select(this.tabItem);
				},
	
				RemoveTab : function(obj)
				{
					if(this.activeTab == obj.tabObject)
					{
						this.target.innerHTML = "";
						this.activeTab = null;
					}
	
					for(var i in this.tabs)
					{
						if(this.tabs[i] == obj.tabObject)
						{
							this.tabs.splice(i, 1);
							obj.tabItem = null;
							obj.tabWidget = null;
	
							return true;
						}
					}
	
					return false;
				},
	
				Select : function(tab)
				{
					if(this.activeTab)
					{
						if(this.defaultClass)
							this.activeTab.obj.className = this.defaultClass;
							
						if(this.defaultImage)
							this.activeTab.img.src = this.inactiveImage;
	
						this.OnDeselect(this.activeTab);
					}
					
					this.activeTab = tab;
	
					if(this.activeClass)
					{
						this.defaultClass = tab.obj.className;
						tab.obj.className = this.activeClass;
					}
					
					if(this.activeImage)
						tab.img.src = this.activeImage;
	
	
					if(this.useHash && tab.name)
						window.location.hash = tab.name;
	
					if(this.useCache && tab.cache)
					{
						if(tab.type == "text" && this.autoTextUpdate)
							this.target.innerHTML = tab.cache;
							
						this.OnUpdate(tab, tab.cache);
					}
					else if(tab.type != "" && (this.link || tab.link))
					{
						var that = this;
						var req = new mint.Request();
	
						req.OnSuccess =
						function()
						{
							switch(tab.type)
							{
								case "text":
								{
									if(that.autoTextUpdate)
										that.target.innerHTML = req.responseText;
										
									if(that.useCache)
										tab.cache = req.responseText;
	
									that.OnUpdate(tab, req.responseText);
									break;
								}
								case "xml":
								{
									if(that.useCache)
										tab.cache = req.responseXML;
	
									that.OnUpdate(tab, req.responseXML);
									break;
								}
								case "json":
								{
									if(that.useCache)
										tab.cache = req.responseJSON;
	
									that.OnUpdate(tab, req.responseJSON);
									break;
								}
								default:
									that.OnUpdate(tab, null);
							}
						}
	
						if(tab.type == "json")
							req.getJSON = true;
	
						if(this.link)
						{
							req.AddParam(this.tabWidgetParam, this.name);
							req.AddParam(this.tabItemParam, tab.name);
								
							req.Send(this.link);
						}
						else
							req.Send(tab.link);
	
						this.OnRetrieve(tab);
					}
					else if(tab.content)
						this.target.innerHTML = tab.content;
						
					this.OnSelect(tab);
				}
			}
		
			target.tabWidget = newTabWidget;
	
			return newTabWidget;
		},
	
		CreateTreeWidget : function(tree, target)
		{
			newTreeWidget = new mint.gui.CreateTabWidget(target);
	
			newTreeWidget.obj = $(tree);
			newTreeWidget.items = new Array();
			newTreeWidget.indent = 25;
			newTreeWidget.selItem = null;
			newTreeWidget.newItemUnfold = true;
			newTreeWidget.useClass = true;
			newTreeWidget.useImage = true;
			newTreeWidget.tabWidgetParam = "treeWidget";
			newTreeWidget.tabItemParam = "treeItem";
			newTreeWidget.OnSelect = function() {};
			newTreeWidget.OnDeselect = function() {};
	
			newTreeWidget.InsertItem = function(parent, name, type, link, text)
			{
				var newItem = 
				{
					obj : document.createElement("div"),
					tab : this.AddTab(document.createElement("span"), name, type, link),
					img : this.useImage ? document.createElement("img") : null,
					area : document.createElement("div"),
					fold : true
				}
	
				newItem.tab.obj.treeItem = newItem;
				newItem.tab.obj.treeWidget = this;
	
				newItem.tab.obj.innerHTML = text || name || "";
	
				newItem.area.style.display = "none";
				newItem.area.style.overflow = "hidden";
				newItem.area.style.marginLeft = this.indent+"px";
	
				newItem.obj.appendChild(newItem.tab.obj);
	
				if(this.useImage && newItem.img)
					newItem.obj.insertBefore(newItem.img, newItem.tab.obj);
	
				this.Update(newItem);
	
				if(parent)
				{
					parent.area.appendChild(newItem.obj);
					parent.area.appendChild(newItem.area);
	
					if(this.newItemUnfold)
					{
						parent.area.style.display = "block";
						parent.fold = false;
					}
	
					if(this.selItem == parent)
						this.selItem = null;
	
					this.Update(parent);
				}
				else
				{
					this.obj.appendChild(newItem.obj);
					this.obj.appendChild(newItem.area);
				}
				
				AddEvent(newItem.tab.obj, "click", this.__SelectTreeItem);
	
				return newItem;
			};
			
			newTreeWidget.__SelectTreeItem = function(item)
			{
				this.treeWidget._Select(this.treeItem);
			};
	
			newTreeWidget._Select = function(item)
			{
				if(this.selItem == item)
				{
					if(item.area.hasChildNodes())
					{
						if(item.fold)
							item.area.style.display = "block";
						else
							item.area.style.display = "none";
	
						item.fold = !item.fold;
					}
				}
				else if(this.selItem)
				{
					var tempItem = this.selItem;
					this.selItem = item;
					this.Update(tempItem);
				}
	
				this.selItem = item;
				this.Update(item);
			};
			
			newTreeWidget.Update = function(item)
			{
				if(this.selItem == item)
				{
					if(item.area.hasChildNodes())
					{
						if(item.fold)
						{
							if(this.useClass)
								item.tab.obj.className = item.foldSelectClass || item.foldClass || item.itemSelectClass || item.itemClass || this.foldSelectClass || this.foldClass || this.itemSelectClass || this.itemClass || item.tab.obj.className;
	
							if(this.useImage)
								item.img.src = item.foldSelectImage || item.foldImage || item.itemSelectImage || item.itemImage || this.foldSelectImage || this.foldImage || this.itemSelectImage || this.itemImage || item.img.src;
						}
						else
						{
							if(this.useClass)
								item.tab.obj.className = item.unfoldSelectClass || item.unfoldClass || item.itemSelectClass || item.itemClass || this.unfoldSelectClass || this.unfoldClass || this.itemSelectClass || this.itemClass || item.tab.obj.className;
							
							if(this.useImage)
								item.img.src = item.unfoldSelectImage || item.unfoldImage || item.itemSelectImage || item.itemImage || this.unfoldSelectImage || this.unfoldImage || this.itemSelectImage || this.itemImage || item.img.src;
						}
					}
					else
					{
						if(this.useClass)
							item.tab.obj.className = item.itemSelectClass || item.itemClass || this.itemSelectClass || this.itemClass || item.tab.obj.className;
	
						if(this.useImage)
							item.img.src = item.itemSelectImage || item.itemImage || this.itemSelectImage || this.itemImage || item.img.src;
					}
				}
				else
				{
					if(item.area.hasChildNodes())
					{
						if(item.fold)
						{
							if(this.useClass)
								item.tab.obj.className = item.foldClass || item.itemClass || this.foldClass || this.itemClass || item.tab.obj.className;
	
							if(this.useImage)
								item.img.src = item.foldImage || item.itemImage || this.foldImage || this.itemImage || item.img.src;
						}
						else
						{
							if(this.useClass)
								item.tab.obj.className = item.unfoldClass || item.itemClass || this.unfoldClass || this.itemClass || item.tab.obj.className;
	
							if(this.useImage)
								item.img.src = item.unfoldImage || item.itemImage || this.unfoldImage || this.itemImage || item.img.src;
						}
					}
					else
					{
						if(this.useClass)
							item.tab.obj.className = item.itemClass || this.itemClass;
	
						if(this.useImage)
							item.img.src = item.itemImage || this.itemImage;
					}
				}
	
				if(item.img.src.match("undefined") != null && item.img.parentNode == item.obj)
					item.obj.removeChild(item.img);
				else if(item.img.src.match("undefined") == null && item.img.parentNode != item.obj)
					item.obj.insertBefore(item.img, item.tab.obj);
			};
	
			return newTreeWidget;
		},
	
		CreateGridWidget : function(grid)
		{
			var newGridWidget =
			{
				id : null,
				obj : $(grid),
				desc : false,
				sortIndex : null,
				selectClass : null,
				defaultClass : null,
				multiSelect : true,
				selRows : new Array(),
				OnSelect : function() {},
				OnDeselect : function() {},
				OnSort : function() {},
				OnStopSort : function() {},
				OnAscSort : function() {},
				OnDescSort : function() {},
				OnDelete : function() {},
	
				AddSortCell : function(index)
				{
					var cell = this.obj.rows[0].cells[index];
	
					cell.gridWidget = this;
					cell.gridSortIndex = index;
					
					cell.onmousedown = function() {return false};
					cell.onselectstart = function() {return false};
	
					AddEvent(cell, "click", this._Sort);
				},
	
				AddSortCells : function(index)
				{
					for(var i = 0; i < arguments.length; i++)
					{
						this.AddSortCell(arguments[i]);
					}
				},
				
				_Sort : function(event)
				{
					this.gridWidget.Sort(this.gridSortIndex);
				},
	
				Sort : function(index)
				{
					var grid = new Array();
					var rows = this.obj.getElementsByTagName("tr");
	
					for(var r = 1; r < rows.length; r++)
					{
						grid[r-1] = rows[r];
					}
	
					if(this.sortIndex != index)
					{
						this.OnStopSort(rows[0].cells[this.sortIndex]);
						this.desc = false;
					}
					else
						this.OnSort(rows[0].cells[index])
	
					this.sortIndex = index;
					
					var that = this;
					var sortItem = grid[0].cells[this.sortIndex].innerHTML;
	
					if(/^\d\d\D\d\d\D\d\d\d\d$/.test(sortItem))
					{
						var sortFunc = function(a, b)
						{
							var reg = /^(\d\d)\D(\d\d)\D(\d\d\d\d)$/; 
						
							a = a.cells[that.sortIndex].innerHTML.replace(reg, "$3$2$1");
							b = b.cells[that.sortIndex].innerHTML.replace(reg, "$3$2$1")
	
							if(a < b) return -1;
							if(a > b) return 1;
							return 0;
						}
					}
					else if(/^\d\d\d\d\D\d\d\D\d\d$/.test(sortItem))
					{
						var sortFunc = function(a, b)
						{
							var reg = /^(\d\d)(\d\d)(\d\d\d\d)$/; 

							a = a.cells[that.sortIndex].innerHTML.split(/\D/).reverse().join("");
							b = b.cells[that.sortIndex].innerHTML.split(/\D/).reverse().join("");
							
							a = a.replace(reg, "$3$2$1");
							b = b.replace(reg, "$3$2$1");
	
							if(a < b) return -1;
							if(a > b) return 1;
							return 0;
						}
					}
					else if(!isNaN(parseInt(sortItem)))
					{
						var sortFunc = function(a, b)
						{
							return parseInt(a.cells[that.sortIndex].innerHTML) - parseInt(b.cells[that.sortIndex].innerHTML);
						}
					}
					else
					{
						var sortFunc = function(a, b)
						{
							a = a.cells[that.sortIndex].innerHTML.toLowerCase();
							b = b.cells[that.sortIndex].innerHTML.toLowerCase();
	
							if(a < b) return -1;
							if(a > b) return 1;
							return 0;
						}
					}
	
					grid.sort(sortFunc);
	
					if(this.desc)
						grid.reverse();
	
					this.desc ? this.OnDescSort() : this.OnAscSort();
					this.desc = !this.desc;
					
					if(this.obj.getElementsByTagName("tbody").length == 0)
						this.obj.appendChild(document.createElement("tbody"));
	
					for(var i in grid)
					{
						this.obj.getElementsByTagName("tbody")[0].appendChild(grid[i]);
					}
				},
	
				SetSelective : function()
				{
					for(var r = 1; r < this.obj.rows.length; r++)
					{
						this.obj.rows[r].gridWidget = this;
						this.obj.rows[r].gridSelect = false;
	
						this.obj.rows[r].onmousedown =
						function(event)
						{
							if(!this.gridSelect)
							{
								this.defaultClass = this.className;
								this.className = this.gridWidget.selectClass || this.className;
								this.gridWidget.selRows.push(this);
	
								this.gridWidget.OnSelect(this);
							}
							else
							{
								for(var i in this.gridWidget.selRows)
								{
									if(this.gridWidget.selRows[i] == this)
									{
										this.gridWidget.selRows.splice(i, 1);
										break;
									}
								}
	
								this.className = this.defaultClass;
								this.gridWidget.OnDeselect(this);
							}
							
							this.gridSelect = !this.gridSelect;
							return false;
						}
					}
				},
				
				GetSelRows : function()
				{
					return this.selRows;
				},
				
				DeleteSelRows : function()
				{
					for(var i in this.selRows)
					{
						this.OnDelete(this.selRows[i]);
						this.obj.deleteRow(this.selRows[i].rowIndex);
					}
					
					this.selRows.length = 0;
				}
			}

			return newGridWidget;
		}
	}
};

mint.gui.Init();

function $(id)
{
	if(typeof(id) == "string")
		return document.getElementById(id);
	else
		return id;
}

function IsF(obj)
{
	if(typeof(obj) == "function")
		return true;
	else
		return false;
}

function AddEvent(obj, type, handler)
{
	obj = $(obj);

	if(!obj.events)
		obj.events = new Array();
		
	if(!obj.events["on"+type])
		obj.events["on"+type] = new Array();

	obj.events["on"+type].push(handler);

	obj["on"+type] =
	function(event)
	{
		event = event || window.event;
	
		var returnValue = true;
		var eventHandlers = this.events["on"+event.type];

		if(eventHandlers)
		{
			for(var i in eventHandlers)
			{
				this.$eventHandler = eventHandlers[i];
				returnValue = this.$eventHandler(event);
			}
		}
	
		return returnValue;
	}
}

function RemoveEvent(obj, type, handler)
{
	obj = $(obj);
	
	var eventHandlers = obj.events["on"+type];

	for(var i in eventHandlers)
	{
		if(eventHandlers[i] == handler)
		{
			eventHandlers.splice(i, 1);
			return true;
		}
	}
	
	return false;
}

function GetEventTarget(event)
{
	return event.target || event.srcElement;
}

function GenerateID()
{
	return new Date().getTime().toString();
}

function GetPos(obj)
{
	obj = $(obj);

	if(obj.style.position == "absolute")
	{
		if(window.getComputedStyle)
			return {'x':parseInt(getComputedStyle(obj, "").getPropertyValue("left")), 'y':parseInt(getComputedStyle(obj, "top").getPropertyValue("top"))};
		else if(obj.currentStyle)
			return {'x':parseInt(obj.currentStyle.left), 'y':parseInt(obj.currentStyle.top)};
	}

	var x = obj.offsetLeft, y = obj.offsetTop, marginLeft = 0, marginTop = 0;

	if(window.getComputedStyle)
	{
		marginLeft = parseInt(getComputedStyle(obj, "").getPropertyValue("margin-left"));
		marginTop = parseInt(getComputedStyle(obj, "").getPropertyValue("margin-top"));
	}
	else if(obj.currentStyle)
	{
		marginLeft = parseInt(obj.currentStyle.marginLeft);
		marginTop = parseInt(obj.currentStyle.marginTop);
	}

	while(obj = obj.offsetParent)
	{
		x += obj.offsetLeft - obj.scrollLeft;
		y += obj.offsetTop - obj.scrollTop;
	}

	if(marginLeft) x -= marginLeft;
	if(marginTop) y -= marginTop;

	return {'x':x, 'y':y};
}

function GetX(obj)
{
	return GetPos(obj).x;
}

function GetY(obj)
{
	return GetPos(obj).y;
}


function SetPos(obj, x, y)
{
	SetX(obj, x);
	SetY(obj, y);
}

function SetX(obj, x)
{
	$(obj).style.left = x+"px";
}

function SetY(obj, y)
{
	$(obj).style.top = y+"px";
}

function GetSize(obj)
{
	return {'width':GetWidth(obj), 'height':GetHeight(obj)};
}

function GetWidth(obj)
{
	return $(obj).clientWidth;
}

function GetHeight(obj)
{
	return $(obj).clientHeight;
}

function SetSize(obj, width, height)
{
	this.SetWidth(obj, width);
	this.SetHeight(obj, height);
}

function SetWidth(obj, width)
{
	$(obj).style.width = width+"px";
}

function SetHeight(obj, height)
{
	$(obj).style.height = height+"px";
}

function GetOpacity(obj)
{
	obj = $(obj);

	if(obj.style.opacity)
		return Math.round(obj.style.opacity*100);
	else if(obj.style.filter)
		return Math.round(/\d+/.exec(obj.style.filter)[0]);
	else
		return 100;
}


function SetOpacity(obj, opacity)
{
	$(obj).style.opacity = opacity*0.01;
	$(obj).style.filter = "alpha(opacity="+opacity+")";
}

function IsInside(obj, x, y)
{
	obj = $(obj);
	var pos = GetPos(obj); size = GetSize(obj);

	if(pos.x < x && pos.x+size.width > x && pos.y < y && pos.y+size.height > y)
		return true;

	return false;
}

function GetChildAtPos(obj, x, y)
{
	var child = $(obj).childNodes;

	for(var i = 0; i < child.length; i++)
	{
		if(child[i].nodeName != "#text" && IsInside(child[i], x, y))
			return child[i];
	}

	return null;
}

function HexToRGB(hex)
{
	hex = hex.replace(/#/, "");

	return {	r: parseInt(hex.substring(0, 2), 16),
				g: parseInt(hex.substring(2, 4), 16),
				b: parseInt(hex.substring(4, 6), 16)};
}