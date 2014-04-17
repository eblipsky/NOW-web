from common import *

HELP = 'Useage:\n'
HELP += '\tenqueue\t\tadd new fq files from [' + DATA_DIR + ']\n'
HELP += '\tshutdown\t\tkills all nodes and stops server process\n'
HELP += '\tkillnode <all|nodeid>\t\tkill 1 or all nodes\n'
HELP += '\tstartnode <all|nodeid>\t\tstart 1 or all nodes\n'
HELP += '\tlistfiles\t\tlist fq files and their id\n'
HELP += '\tlistqueues\t\tlist queues and their id\n'
HELP += '\tmovequeue <qfromid> <qtoid>\t\tpop from one q to another q\n'
HELP += '\taddqueue <fileid> <queueid>\t\tadd fq files to queue\n'

##############################################################
#
#############################################################
def Main():

    r = None

    if (len(sys.argv)==1):
        print HELP
        exit(1)

    if (sys.argv[1] == 'startserver'):
        server_start()
    elif (sys.argv[1] == 'parsecmd'):
        print cmd_parse(sys.argv[2],sys.argv[3:],'abc_xyz')
    elif (sys.argv[1] == 'enqueue'):
        review_queues()
    elif (sys.argv[1] == 'shutdown'):
        node_stop_all()
        server_stop()
    elif (sys.argv[1] == 'killnode'):
        if (sys.argv[2]=='all'):
            node_stop_all()
        else:
            node_stop(int(sys.argv[2]))
    elif (sys.argv[1] == 'startnode'):
        if (sys.argv[2]=='all'):
            node_start_all()
        else:
            node_start(int(sys.argv[2]))
    elif (sys.argv[1] == 'listpipelines'):
        r = get_client()
        cnt=0
        for fq in r.smembers('pipeline'):
            print cnt,fq
            cnt += 1
    elif (sys.argv[1] == 'listfiles'):
        r = get_client()
        cnt=0
        for fq in r.smembers('fq'):
            print cnt,fq
            cnt += 1
    elif (sys.argv[1] == 'listnodes'):
        r = get_client()
        for n in NODE_IDS:
            node = 'hpcdarwinnode' + str(n)
            if node in r.smembers('nodes'):
                print node,"ON"
            else:
                print node,"OFF"
    elif (sys.argv[1] == 'listqueues'):
        cnt=0
        for q in QUEUES:
            print cnt, q
            cnt += 1
    elif (sys.argv[1] == 'movequeue'):
        r = get_client()
        qfrom = int(sys.argv[2])
        qto = int(sys.argv[3])
        while (r.llen(QUEUES[qfrom]) > 0):
            r.rpush(QUEUES[qto],r.lpop(QUEUES[qfrom]))
    elif (sys.argv[1] == 'addqueue'):
        r = get_client()
        cnt=0
        for fq in r.smembers('fq'):
            if (str(cnt) == str(sys.argv[2])):
                print 'moving ' + fq + ' to ' + QUEUES[sys.argv[3]]
                r.rpush( QUEUES[sys.argv[3]], fq )
            cnt += 1
    #todo: add method to move item and or check if its not in done and not being worked on
    else:
        print HELP
        exit(1)


################################################################
if __name__ == '__main__':
    Main()
