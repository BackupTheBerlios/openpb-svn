var opfMapType = new function()
{
	this.MAP_TYPE = 0;
	this.MAP_GT = 1;
	this.MAP_LT = 2;
	this.MAP_LEN_GT = 3;
	this.MAP_LEN_LT = 4;
	this.MAP_EQUAL = 5;
	this.MAP_LEN_EQUAL = 6;
	this.MAP_PASSWORD = 7;
	this.MAP_MATCHTO = 8;
	this.MAP_NOTMATCHTO = 9;
	this.MAP_PERMITTEDCHARS = 10;
	this.MAP_NOTPERMITTEDCHARS = 11;
	this.MAP_SCOPE = 12;
	this.MAP_JS = 13;
	
	this.TYPE_INTEGER = 0;
	this.TYPE_FLOAT = 1;
	this.TYPE_NUMERIC = 2;
	this.TYPE_STRING = 3;
	this.TYPE_TEXT = 4;
	this.TYPE_BOOL = 5;
	this.TYPE_BOOLEAN = 5;
	this.TYPE_CHOOSE = 6;
	this.TYPE_COMPARABLE = 7;

	this.JS_SUBMIT = 2;
	this.JS_SELECT = 4;
	this.JS_CHANGE = 8;
	this.JS_RESET = 16;
	this.JS_FOCUS = 32;
	this.JS_BLUR = 64;
	this.JS_KEYPRESS = 128;
	this.JS_KEYUP = 256;
	this.JS_KEYDOWN = 512;
	
	this.OPF_MAIL_PATTERN = '/(.+)\@(.+)\.(.+)/';
}