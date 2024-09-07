let s;
let login = `
    <input id="name" onkeydown="
        if (event.key === 'Enter' && this.value !== '') {
            send(s, [this.value, color], 'name');
            document.querySelector('#fails').innerHTML = '';
            document.querySelector('#msgs').innerHTML = login;
        }
    ">
    <button onclick="
        if (document.querySelector('#name').value !== '') {
            send(s, document.querySelector('#name').value, 'name');
            document.querySelector('#fails').innerHTML = '';
            document.querySelector('#msgs').innerHTML = login;
        }
    ">login</button>
`;
let sen = `
        <input id="send" onkeydown="
            if (event.key === 'Enter') {
                send(s, this.value);
                this.value = '';
                document.querySelector('#fails').innerHTML = '';
            }    
        ">
        <button onclick="
            send(s, document.querySelector('#send').value);
            document.querySelector('#send').value = '';
            document.querySelector('#fails').innerHTML = '';
        ">send</button> <button onclick="
            send(s, 'room', 'leave');
            document.querySelector('#game').style = '';
            document.querySelector('#game').innerHTML = '';
            document.querySelector('#msgs').innerHTML = cr;
            document.querySelector('#fails').innerHTML = '';
        ">leave room</button><br>
    `;
let cr = `<input id="cr" onkeydown="
            if (event.key === 'Enter') {
                if (this.value == '') {
                    this.value = 'default'
                }
                send(s, this.value, 'create');
                this.value = '';
            }    
        ">
        <button onclick="
            if (document.querySelector('#cr').value == '') {
                document.querySelector('#cr').value = 'default'
            }
            send(s, document.querySelector('#cr').value, 'create');
            document.querySelector('#cr').value = '';
        ">create</button><br>
    `;
let pos;
let color = [];
color[0] = Math.floor(Math.random() * (255 - 0 + 1)) + 0
color[1] = Math.floor(Math.random() * (255 - 0 + 1)) + 0
color[2] = Math.floor(Math.random() * (255 - 0 + 1)) + 0

let connect = function(name) {
    const s = new WebSocket('ws://192.168.68.72:5001');
    s.onopen = function() {
        if (name !== '') {
            send(s, [name, color], 'name');
        }
        else {
            document.querySelector('#fails').innerHTML = 'please write a name';
        }
    };
    s.onmessage = function(e) {
        let header = JSON.parse(e.data)[0];
        let msg = JSON.parse(e.data)[1];
        if (header === 'msg'){
            document.querySelector('#msgs').innerHTML += `${msg}<br>`;
        }
        else if (header === 'rooms') {
            let text = "";
            for (let i = 0; i < msg.length; i++) {
                text += msg[i] + ` <button onclick="
                    send(s, \`${msg[i]}\`, 'join')
                    document.querySelector('#msgs').innerHTML = sen;
                    document.querySelector('#rooms').innerHTML = '';
                    document.querySelector('#fails').innerHTML = '';
                ">join</button><br>`;
            }
            document.querySelector("#rooms").innerHTML = text;
        }
        else if (header === 'fail'){
            document.querySelector('#fails').innerHTML = msg;
        }
        else if (header === 'success') {
            if (msg === 'room') {
                document.querySelector('#msgs').innerHTML = sen;
                document.querySelector('#rooms').innerHTML = '';
                document.querySelector('#fails').innerHTML = '';
                document.querySelector("#game").style.width = '500px';
                document.querySelector("#game").style.height = '500px';
                document.querySelector("#game").style.border = 'black';
                document.querySelector("#game").style.borderWidth = '1px';
                document.querySelector("#game").style.borderStyle = 'solid';
                pos = [0, 0];
                send(s, pos, 'move');
            }
            if (msg === 'name') {
                document.querySelector('#msgs').innerHTML = cr;
            }
        }
        else if (header === 'move') {
            document.querySelector("#game").innerHTML = msg
        }
        else if (header === 'rm_name') {
            if (msg === '') {
                document.querySelector("#name").innerHTML = msg;
            }
            else {
                document.querySelector("#name").innerHTML = `room: ${msg}`;
            }
        }
        else if (header === 'rm_ppl') {
            if (msg === '') {
                document.querySelector("#ppl").innerHTML = '';
            }
            else {
                let txt = '';
            for (let i = 0; i < msg.length; i++) {
                if (i !== msg.length - 1) {
                    txt += msg[i] + ', ';
                }
                else {
                    txt += msg[i];
                }
            }
            document.querySelector("#ppl").innerHTML = `participants: ${txt.replace(document.querySelector("#usrname").innerHTML, "you")}`;
            }
        }
        else if (header === 'name') {
            document.querySelector('#username').innerHTML = `username:`;
            document.querySelector('#usrname').innerHTML = `${msg}`;
        }
        else if (header === 'ate') {
            pos = [0, 0];
        }
    }
    return s
}


let move = function(e) {
    let focus = document.activeElement;
    if (focus === document.body) {
        if (e.key === 'ArrowUp') {
            if (pos[0] !== 0) {
                pos[0] -= 14;
                send(s, pos, 'move');
            }
        }
        else if (e.key === 'ArrowDown') {
            if (pos[0] + 30 !== 492) {
                pos[0] += 14;
                send(s, pos, 'move');
            }
        }
        else if (e.key === 'ArrowRight') {
            if (pos[1] + 30 !== 492) {
                pos[1] += 14;
                send(s, pos, 'move');
            }
        }
        else if (e.key === 'ArrowLeft') {
            if (pos[1] !== 0) {
                pos[1] -= 14;
                send(s, pos, 'move');
            }
        }
        else if (e.key === ' ') {
            send(s, pos, 'eat');
        }
    }
}


document.addEventListener('keydown', move)


let send = function(s, msg, header = 'msg') {
    if (msg !== '') {
        if (header === 'msg') {
            s.send(JSON.stringify([header, msg]))
            document.querySelector('#msgs').innerHTML += `<span style="color:rgb(${color[0]},${color[1]},${color[2]});">you</span>: ${msg}<br>`;
        }
        else {
            s.send(JSON.stringify([header, msg]))
        }
    }
}
