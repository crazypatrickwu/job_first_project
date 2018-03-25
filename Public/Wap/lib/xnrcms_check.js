var CheckJs={
		// 必填
		required: function( value ) {
			return value.length > 0;
		},
		// 邮箱验证
		email: function( value ) {
			return /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test( value );
		},
		mobile:function( value ){
			return /^1([3-9][0-9])\d{8}$/.test( value );
		},
		// URL合法验证
		url: function( value ) {
			return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i.test( value );
		},
		// 合法的日期验证
		date: function( value ) {
			return !/Invalid|NaN/.test( new Date( value ).toString() );
		},
		// 合法的日期 (ISO)验证
		dateISO: function( value ) {
			return this.optional( element ) || /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test( value );
		},
		// 数字验证
		number: function( value ) {
			return /^(?:-?\d+|-?\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test( value );
		},
		// 只能输入整数
		digits: function( value ) {
			return /^\d+$/.test( value );
		},
		// 合法的信用卡号验证
		creditcard: function( value, element ) {
			if ( /[^0-9 \-]+/.test( value ) ) { return false;}
			var nCheck = 0,nDigit = 0,bEven = false,n, cDigit;
				value = value.replace( /\D/g, "" );
			if ( value.length < 13 || value.length > 19 ) { return false;}
			for ( n = value.length - 1; n >= 0; n--) {
				cDigit = value.charAt( n );
				nDigit = parseInt( cDigit, 10 );
				if ( bEven ) {
					if ( ( nDigit *= 2 ) > 9 ) {
						nDigit -= 9;
					}
				}
				nCheck += nDigit;
				bEven = !bEven;
			}
			return ( nCheck % 10 ) === 0;
		},
		minlength: function( value,param ) {
			var length = value.length;
			return length >= param;
		},
		maxlength: function( value, param ) {
			var length = value.length;
			return length <= param;
		},
		rangelength: function( value, param ) {
			var length = value.length;
			return ( length >= param[ 0 ] && length <= param[ 1 ] );
		},
		min: function( value, param ) {
			return value >= param;
		},
		max: function( value, param ) {
			return value <= param;
		},
		range: function( value, param ) {
			return ( value >= param[ 0 ] && value <= param[ 1 ] );
		},
		equalTo: function( value,param ) {
			return value === param;
		},
		//中文验证
		chinese:function(value){
			return /^[\u4e00-\u9fa5]+$/.test(value);
		},
};