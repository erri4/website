from websocket_server import WebsocketServer
import json

clients = []
clients_name = []
rooms = {}
game = {}


def send(msg, cl, server, header = 'msg'):
    server.send_message(cl, json.dumps([header, msg]))


def get_rooms():
    rooms_nm = list(rooms.keys())
    return rooms_nm


def get_participants(room):
    r = []
    for cl in rooms[f'{room}']:
        r.append(get_cl_name(cl))
    return r


def get_cl_name(client):
    for cl in clients_name:
        if cl[0] == client['id']:
            return cl[1]
        

def get_cl_color(client_nm):
    for cl in clients_name:
        if cl[1] == client_nm:
            return cl[2]


def create_room(name, creator):
    ex = False
    cl_name = get_cl_name(creator)
    if name == True:
        name = f"{cl_name}'s room"
    for room in get_rooms():
        if room == name:
            ex = True
    if ex == False:
        game[f'{name}'] = {}
        rooms[f'{name}'] = [creator]
        game[f'{name}'][f'{cl_name}'] = ['0', '0']
        print(f'room created: {name}')
        return [True, name]
    else:
        print('room already exist')
        return False
    
def create_acc(cl, name, color):
    ex = False
    for cli in clients_name:
        if cli[1] == name:
            ex = True
    if ex == False:
        clients_name.remove([cl['id'], None])
        clients_name.append([cl['id'], name, color])
        print(f"New client connected: {name}")
        return True
    else:
        print('user already exist')
        return False


def get_cr_rm(client):
    for room in get_rooms():
        for cl in rooms[f'{room}']:
            if cl == client:
                return room
    return False
            

def join_room(cl, room):
    cl_name = get_cl_name(cl)
    rooms[f'{room}'].append(cl)
    game[f'{room}'][f'{cl_name}'] = ['0', '0']


def leave_room(cl):
    cl_name = get_cl_name(cl)
    room = get_cr_rm(cl)
    rooms[f'{room}'].remove(cl)
    game[f'{room}'].pop(f'{cl_name}')
    if rooms[f'{room}'] == []:
        rooms.pop(f'{room}')
        game.pop(f'{room}')
        return False
    return True


def new_client(client, server):
    clients.append(client)
    clients_name.append([client['id'], None])


def client_left(client, server):
    cl_name = get_cl_name(client)
    if get_cr_rm(client) != False:
        cr_rm = get_cr_rm(client)
        lv = leave_room(client)
        if lv == True:
            play = ''
            for player in list(game[f'{cr_rm}'].keys()):
                play += f'<div class="player" style="top:{game[f'{cr_rm}'][f'{player}'][0]}px;left:{game[f'{cr_rm}'][f'{player}'][1]}px;background-color:rgb({get_cl_color(player)[0]},{get_cl_color(player)[1]},{get_cl_color(player)[2]});"><div class="name">{player}</div></div>'
            for cl in rooms[f'{cr_rm}']:
                    if cl != client:
                        send(f'<span class="sys_msg">*{cl_name} have left the room*</span>', cl, server)
                        send(get_participants(cr_rm), cl, server, 'rm_ppl')
                        send(play, cl, server, 'move')
    clients_name.remove([client['id'], cl_name, get_cl_color(cl_name)])
    clients.remove(client)
    for cl in clients:
            if get_cr_rm(cl) == False:
                send(list(get_rooms()), cl, server, 'rooms')
    print(f"Client disconnected: {cl_name}")

def message_received(client, server, msg):
    cl_name = get_cl_name(client)
    msg = json.loads(msg)
    header = msg[0]
    msg = msg[1]
    if cl_name == None and header == 'name':
        acc = create_acc(client, msg[0], msg[1])
        if acc == True:
            send('name', client, server, 'success')
            send(msg[0], client, server, 'name')
            send(list(get_rooms()), client, server, 'rooms')
        else:
            send('user already exist', client, server, 'fail')
    elif header == 'msg':
        print(f"{cl_name}: {msg}")
        reply = f'<span style="color:rgb({get_cl_color(cl_name)[0]},{get_cl_color(cl_name)[1]},{get_cl_color(cl_name)[2]});">{cl_name}</span>: {msg}'
        for cl in rooms[f'{get_cr_rm(client)}']:
            if cl != client:
                send(reply, cl, server)
    elif header == 'join':
        for cl in rooms[f'{msg}']:
            send(f'<span class="sys_msg">*{cl_name} have joined the room*</span>', cl, server)
        join_room(client, msg)
        send(msg, client, server, 'rm_name')
        send('room', client, server, 'success')
        for cl in rooms[f'{msg}']:
            send(get_participants(msg), cl, server, 'rm_ppl')
        play = ''
        for player in list(game[f'{msg}'].keys()):
            play += f'<div class="player" style="top:{game[f'{msg}'][f'{player}'][0]}px;left:{game[f'{msg}'][f'{player}'][1]}px;background-color:rgb({get_cl_color(player)[0]},{get_cl_color(player)[1]},{get_cl_color(player)[2]});"><div class="name">{player}</div></div>'
        for cl in rooms[f'{msg}']:
            send(play, cl, server, 'move')
    elif header == 'create':
        if msg == 'default':
            msg = True
        cr = create_room(msg, client)
        if cr == False:
            send('room already exist', client, server, 'fail')
        else:
            send('room', client, server, 'success')
            send(cr[1], client, server, 'rm_name')
            send([get_cl_name(client)], client, server, 'rm_ppl')
            for cl in clients:
                if get_cr_rm(cl) == False:
                    send(list(get_rooms()), cl, server, 'rooms')
            play = ''
            for player in list(game[f'{cr[1]}'].keys()):
                play += f'<div class="player" style="top:{game[f'{cr[1]}'][f'{player}'][0]}px;left:{game[f'{cr[1]}'][f'{player}'][1]}px;background-color:rgb({get_cl_color(player)[0]},{get_cl_color(player)[1]},{get_cl_color(player)[2]});"><div class="name">{player}</div></div>'
            for cl in rooms[f'{cr[1]}']:
                send(play, cl, server, 'move')
    elif header == 'leave':
        rm = get_cr_rm(client)
        lv = leave_room(client)
        if lv == True:
            play = ''
            for player in list(game[f'{rm}'].keys()):
                play += f'<div class="player" style="top:{game[f'{rm}'][f'{player}'][0]}px;left:{game[f'{rm}'][f'{player}'][1]}px;background-color:rgb({get_cl_color(player)[0]},{get_cl_color(player)[1]},{get_cl_color(player)[2]});"><div class="name">{player}</div></div>'
            for cl in rooms[f'{rm}']:
                    if cl != client:
                        send(f'<span class="sys_msg">*{cl_name} have left the room*</span>', cl, server)
                        send(play, cl, server, 'move')
        else:
            for cl in clients:
                if get_cr_rm(cl) == False:
                    send(list(get_rooms()), cl, server, 'rooms')
        send(list(get_rooms()), client, server, 'rooms')
        send('', client, server, 'rm_name')
        send('', client, server, 'rm_ppl')
    elif header == 'move':
        room = get_cr_rm(client)
        game[f'{room}'][f'{cl_name}'] = [f'{msg[0]}', f'{msg[1]}']
        play = ''
        for player in list(game[f'{room}'].keys()):
            play += f'<div class="player" style="top:{game[f'{room}'][f'{player}'][0]}px;left:{game[f'{room}'][f'{player}'][1]}px;background-color:rgb({get_cl_color(player)[0]},{get_cl_color(player)[1]},{get_cl_color(player)[2]});"><div class="name">{player}</div></div>'
        for cl in rooms[f'{room}']:
            send(play, cl, server, 'move')
    elif header == 'eat':
        room = get_cr_rm(client)
        for player in list(game[f'{room}'].keys()):
            if player != get_cl_name(client):
                posp = game[f'{room}'][f'{player}']
                if msg[0] < int(posp[0]) + 29 and msg[0] > int(posp[0]) - 29:
                    if msg[1] < int(posp[1]) + 29 and msg[1] > int(posp[1]) - 29:
                        print(f'{get_cl_name(client)} ate {player}')
                        game[f'{room}'][f'{player}'] = ['0', '0']
                        play = ''
                        for player1 in list(game[f'{room}'].keys()):
                            play += f'<div class="player" style="top:{game[f'{room}'][f'{player1}'][0]}px;left:{game[f'{room}'][f'{player1}'][1]}px;background-color:rgb({get_cl_color(player1)[0]},{get_cl_color(player1)[1]},{get_cl_color(player1)[2]});"><div class="name">{player1}</div></div>'
                        for cl in rooms[f'{room}']:
                            send(play, cl, server, 'move')
                            if get_cl_name(cl) == player:
                                send('', cl, server, 'ate')
                            send(f'<span class="sys_msg">*<span style="color:rgb({get_cl_color(get_cl_name(client))[0]},{get_cl_color(get_cl_name(client))[1]},{get_cl_color(get_cl_name(client))[2]});">{get_cl_name(client)}</span> ate <span style="color:rgb({get_cl_color(player)[0]},{get_cl_color(player)[1]},{get_cl_color(player)[2]});">{player}</span>*</span>', cl, server)


def start_server():
    server = WebsocketServer(host='192.168.68.72', port=5001)
    server.set_fn_new_client(new_client)
    server.set_fn_client_left(client_left)
    server.set_fn_message_received(message_received)
    
    print("Server listening on 192.168.68.72:5001")
    server.run_forever()

if __name__ == "__main__":
    start_server()