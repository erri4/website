from websocket_server import WebsocketServer
import json

clients = []
clients_name = []
rooms = {}


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


def create_room(name, creator):
    ex = False
    if name == True:
        name = f"{get_cl_name(creator)}'s room"
    for room in get_rooms():
        if room == name:
            ex = True
    if ex == False:
        rooms[f'{name}'] = [creator]
        print(f'room created: {name}')
        return [True, name]
    else:
        print('room already exist')
        return False
    
def create_acc(cl, name):
    ex = False
    for cli in clients_name:
        if cli[1] == name:
            ex = True
    if ex == False:
        clients_name.remove([cl['id'], None])
        clients_name.append([cl['id'], name])
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
    rooms[f'{room}'].append(cl)


def leave_room(cl):
    room = get_cr_rm(cl)
    rooms[f'{room}'].remove(cl)
    if rooms[f'{room}'] == []:
        rooms.pop(f'{room}')


def new_client(client, server):
    clients.append(client)
    clients_name.append([client['id'], None])


def client_left(client, server):
    cl_name = ''
    for cl in clients_name:
        if cl[0] == client['id']:
            cl_name = cl[1]
    clients_name.remove([client['id'], cl_name])
    clients.remove(client)
    if get_cr_rm(client) != False:
        for cl in rooms[f'{get_cr_rm(client)}']:
                if cl != client:
                    send(f'<span class="sys_msg">*{cl_name} have left the room*</span>', cl, server)
        leave_room(client)
    print(f"Client disconnected: {cl_name}")

def message_received(client, server, msg):
    cl_name = get_cl_name(client)
    msg = json.loads(msg)
    header = msg[0]
    msg = msg[1]
    if cl_name == None and header == 'name':
        acc = create_acc(client, msg)
        if acc == True:
            send('name', client, server, 'success')
            send(msg, client, server, 'name')
            send(list(get_rooms()), client, server, 'rooms')
        else:
            send('user already exist', client, server, 'fail')
    elif header == 'msg':
        print(f"{cl_name}: {msg}")
        reply = f"<span class='names'>{cl_name}</span>: {msg}"
        for cl in rooms[f'{get_cr_rm(client)}']:
            if cl != client:
                send(reply, cl, server)
    elif header == 'join':
        for cl in rooms[f'{msg}']:
            send(f'<span class="sys_msg">*{cl_name} have joined the room*</span>', cl, server)
        join_room(client, msg)
        send(msg, client, server, 'rm_name')
        for cl in rooms[f'{msg}']:
            send(get_participants(msg), cl, server, 'rm_ppl')
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
    elif header == 'leave':
        for cl in rooms[f'{get_cr_rm(client)}']:
                if cl != client:
                    send(f'<span class="sys_msg">*{cl_name} have left the room*</span>', cl, server)
        leave_room(client)
        for cl in clients:
            if get_cr_rm(cl) == False:
                send(list(get_rooms()), cl, server, 'rooms')
        send('', client, server, 'rm_name')
        send('', client, server, 'rm_ppl')

def start_server():
    server = WebsocketServer(host='192.168.68.74', port=5001)
    server.set_fn_new_client(new_client)
    server.set_fn_client_left(client_left)
    server.set_fn_message_received(message_received)
    
    print("Server listening on 192.168.68.74:5001")
    server.run_forever()

if __name__ == "__main__":
    start_server()