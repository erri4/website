let encode_entty = function(str) {
    var r = [];
    str.split('').forEach((v) => {
        r.push(`&#${v.charCodeAt()};`);
    });
    return r.join('');
}

let decode_entty = function(str) {
    return str.replace(/&#(\d+);/g, function(match, dec) {
        return String.fromCharCode(dec);
    });
}
let encode_url = function(str, everything) {
    if(everything){
        return encodeURIComponent(str);
    }
    else{
        return encodeURI(str);
    }
}
let decode_url = function(str) {
    return decodeURIComponent(str);
}
let reverse = function(arr) {
    return arr.reverse();
}
let func = function(ths) {
    send_http_req({vortex: `${ths.value}`},
    'POST',
    'try.php',
    '#vortex',
    (r) => {
        document.querySelector('#vortex').innerHTML = JSON.parse(r)[0] + '<br>' + JSON.parse(r)[1].join(', ')
    });
}