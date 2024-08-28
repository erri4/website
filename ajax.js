let send_http_req = function(obj = {}, method = "POST", to = "server.php", result_trgt = "body", func = (r) => {document.querySelector(`${result_trgt}`).innerHTML = `${r}`;}) {
    if (typeof obj == "object") {
        httpreq = new XMLHttpRequest();
        httpreq.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                if(this.responseText !== ""){
                    if(this.responseText == true){
                        document.querySelector(`${result_trgt}`).innerHTML = ``;
                        return this.responseText;
                    }
                    else{
                        func(this.responseText);
                        return this.responseText;
                    }
                }
            }
            else if (this.status !== 200 && this.status !== "" && this.status !== 0) {
                console.log(this.status);
                return this.statusText;
            }
        };
        arr = [];
        Object.keys(obj).forEach((v) => {
            arr.push(`${v}=${obj[`${v}`]}`);
        });
        txt = encodeURI(arr.join("&"));
        if (method.toUpperCase() === "GET"){
            httpreq.open(`${method.toUpperCase()}`, `${to}?${txt}`, true);
            httpreq.send();
        }
        else if(method.toUpperCase() === "POST"){
            httpreq.open(`${method.toUpperCase()}`, `${to}`, true);
            httpreq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            httpreq.send(`${txt}`);
        }
    }
}