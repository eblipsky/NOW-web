#!/usr/bin/python
import time
from datetime import datetime
import os
import sys
import socket
import redis
from subprocess import Popen

# this is user set
BASE_DIR = '/opt/NOW'

#REF_GENOME = 'hg19.fa'
#REF_VCF = 'hg19.dbsnp.vcf'
REF_GENOME = 'human_g1k_v37.fasta'
REF_VCF = '00-All.vcf'
BED = '120127_HG19_HGSC_PGRNseq_EZ.nochr.bed'

MAX_CPU = 16
NODE_IDS = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19]
#NODE_IDS = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24]

NODE_BASE_NAME = 'hpcdarwinnode'
REDIS_HOST = 'localhost'
REDIS_PORT = 6379
REDIS_DB = 0

UG_NCT = 4
UG_NT = 16

DATE_FMT = "%Y%m%d %H:%M:%S"
DATE_FMT_WO = "%H:%M:%S"
# below here should be dynamic
RET_OK = 0
RET_NO_WORK = -1
RET_ERROR = 1

REF_DIR = BASE_DIR + "/ref"
REF_HUMAN_GENOME = REF_DIR + '/' + REF_GENOME
REF_BED = REF_DIR + '/' + BED
DATA_DIR = BASE_DIR + "/data"
STAT_DIR = BASE_DIR + "/stats"
LOG_DIR = BASE_DIR + "/log"
HOSTNAME = socket.gethostname()
WORK_DIR = BASE_DIR + "/data/" + HOSTNAME
SCRIPT_CLI = BASE_DIR + '/tools/src/pipeline/process-cli.py'
USERNAME = 'eblipsky' #os.getlogin()

server_process = []
node_processes = []

r = None
##################################################################
def listdir_fullpath(d):
    return [os.path.join(d, f) for f in os.listdir(d)]

###########################################################
def cmd_parse(pipeline,cmd,fq):
    r = get_client()
    cmd = str(cmd)

    # todo: this is a hack.. make it better
    intervals = eval(r.hget('gvars','intervals'))
    while ( '%intervals%' in cmd):
        spos = cmd.find('%intervals%')
        epos = spos + len('%intervals%')
        if ('.' in fq):
            sub = int(fq.split('.')[-1])
            cmd = cmd[:spos] + intervals[sub-1] + cmd[epos:]
        else:
            cmd = cmd[:spos] + '' + cmd[epos:]

    # replace file specific vars
    vars = r.hkeys('var_'+fq)
    for var in vars:
        while ( '%fq['+var+']%' in cmd):
            spos = cmd.find('%fq['+var+']%')
            epos = spos + len(var) + 6
            cmd = cmd[:spos] + str(r.hget('var_'+fq,var)) + cmd[epos:]

    while '%fq%' in cmd:
        spos = cmd.find('%fq%')
        epos = spos + len('%fq%')
        cmd = cmd[:spos] + DATA_DIR + '/' + fq + '/' + fq + cmd[epos:]

    # replace globals
    vars = r.hkeys('gvars')
    for var in vars:
        while ( '%'+var+'%' in cmd):
            spos = cmd.find('%'+var+'%')
            epos = spos + len(var) + 2
            cmd = cmd[:spos] + str(r.hget('gvars',var)) + cmd[epos:]

    gvars = [['REF_HUMAN_GENOME','ref_genome'],['REF_VCF','ref_vcf'],['REF_BED','ref_bed'],['BAIT_BED','ref_bait'],['TARGET_BED','ref_target']]
    for var,rname in gvars:
        while ( '%'+var+'%' in cmd):
            spos = cmd.find('%'+var+'%')
            epos = spos + len(var) + 2
            cmd = cmd[:spos] + eval('REF_DIR') + '/' + str(r.hget(pipeline,rname)) + cmd[epos:]

    # replace global vars
    while ( '%' in cmd ):
        spos = cmd.find('%')
        epos = -1
        for i in range (spos+1,len(cmd)):
            if cmd[i] == '%':
                epos=i
                break
        cmd = cmd[:spos] + str(eval(cmd[spos+1:epos])) + cmd[epos+1:]

    # eval function calls
    while ( '~' in cmd ):
        spos = cmd.find('~')
        epos = -1
        for i in range (spos+1,len(cmd)):
            if cmd[i] == '~':
                epos=i
                break
        cmd = cmd[:spos] + str(eval(cmd[spos+1:epos])) + cmd[epos+1:]

    return cmd

##################################################################
def server_start():
    cmd = 'redis-server --dir ' + DATA_DIR
    server_process.append(Popen(cmd, shell=True))
    time.sleep(.5)
    #server_process[0].poll()
    #server_process[0].returncode    

##################################################################
def server_stop():
    r = get_client()
    r.execute_command('shutdown save')
    #server_process[0].kill()

##################################################################
def get_client():
    global r
    try:
        if r is None:
            r = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, db=REDIS_DB)
        return r
    except:
        return None

##################################################################
def node_start_all():
    for i in range(len(NODE_IDS)):
        node_start(NODE_IDS[i])

##################################################################
def isNodeRunning(n):
    node = NODE_BASE_NAME + str(n)
##################################################################
def node_start(n):
    if (n not in NODE_IDS):
        print 'node ' + str(n) + ' not in NODE_IDS'
        exit(1)
    command = 'ssh -f ' + NODE_BASE_NAME + str(n) + ' \'cd ' + BASE_DIR + ';. tools/env_setup.sh; (python ' + SCRIPT_CLI + ') >> ' + LOG_DIR + '/process_' + str(n) + '.log 2>&1 &\''
    print 'starting node ' + str(n)

    Popen(command, shell=True)
    time.sleep(.5)

##################################################################
def node_stop_all():
    for i in range(len(NODE_IDS)):
        node_stop(NODE_IDS[i])

##################################################################
def node_stop(n):
    if (n not in NODE_IDS):
        print 'node ' + str(n) + 'not in NODE_IDS'
        exit(1)

    r = get_client()
    command2 = 'ssh ' + NODE_BASE_NAME + str(n) + ' \'pkill -2 -u ' + USERNAME + '\' >/dev/null 2>&1'
    command9 = 'ssh ' + NODE_BASE_NAME + str(n) + ' \'pkill -9 -u ' + USERNAME + '\' >/dev/null 2>&1'

    print "killing " + NODE_BASE_NAME + str(n)
    Popen(command2, shell=True)
    time.sleep(.5)
    Popen(command9, shell=True)
    time.sleep(.5)

##################################################################
def set_cmd(cmd=[]):
    r = get_client()
    r.set('cmd_'+HOSTNAME,cmd)

##################################################################
def set_file_info(fq,stage,start,end,err=0):
    r = get_client()
    tmp = r.get('fq_time_' + fq)
    if tmp is None:
        tmp = datetime.now().strftime(DATE_FMT)

    if (type(err) == str):
        r.set('fq_time_' + fq, tmp + '|' + stage + '^' + HOSTNAME + '^' + start.strftime(DATE_FMT) + '^' + end.strftime(DATE_FMT) + '^' + err)
    else:
        if (err != 0):
            r.set('fq_time_' + fq, tmp + '|' + stage + '^' + HOSTNAME + '^' + start.strftime(DATE_FMT) + '^' + end.strftime(DATE_FMT) + '^!!ERROR!!')
        else:
            h, remain = divmod((end-start).seconds,3600)
            m, s = divmod(remain,60)
            r.set('fq_time_' + fq, tmp + '|' + stage + '^' + HOSTNAME + '^' + start.strftime(DATE_FMT) + '^' + end.strftime(DATE_FMT) + '^' + str(h)+':'+str(m)+':'+str(s))

##################################################################
def set_node_info(stage='?',start=None):
    r = get_client()
    r.sadd('nodes',HOSTNAME)
    r.hset(HOSTNAME,'user',USERNAME)
    r.hset(HOSTNAME,'stage',stage)
    if start != None:
        r.hset(HOSTNAME,'start',start.strftime("%H:%M:%S"))

#############################################################
def get_q_len():
    r = get_client()
    total_q_len = r.llen('fq->sai')
    total_q_len += r.llen('sai->sam')
    total_q_len += r.llen('sam->bam')
    total_q_len += r.llen('sort_index_bam')
    total_q_len += r.llen('base_recal_print_reads')
    total_q_len += r.llen('index_bam_report')
    return total_q_len

##################################################################
def get_file_sizes():
    r = get_client()
    for fq in r.smembers('fq'):
        s = os.stat(DATA_DIR + '/' + fq + '_1.fq').st_size
        s += os.stat(DATA_DIR + '/' + fq + '_2.fq').st_size
        r.set('fq_size_' + fq, str(round((s*9.31322574615E-10),2)) + ' GB')

##################################################################
def review_queues():
    r = get_client()

    # check data folder for new files and add them
    for fq in os.listdir(DATA_DIR):
        fname, ext = os.path.splitext(fq)
        if ext != '.fq':
            continue
        if (r.sismember('fq',fname[:-2]) == False):
            r.set('fq_time_' + fname[:-2], 'enqueue:' + datetime.now().strftime("%Y%m%d %H:%M:%S"))
            r.sadd('fq',fname[:-2])
            s = os.stat(DATA_DIR + '/' + fq).st_size
            est = str(round((((s*9.31322574615E-10)/0.0533)/60),2)) + ' hours'
            r.set('fq_time_est_' + fname[:-2], est)
            #r.rpush('fq->sai', fname[:-2])

    get_file_sizes()

##################################################################
def start_file(fq,pipeline):
    r.rpush(pipeline+'_queue_start', fq)

##################################################################
def cleanup():
    r.srem('nodes',HOSTNAME)
    r.ltrim(HOSTNAME+'_files',1,0)
    r.hdel(HOSTNAME,'ver')
    r.hdel(HOSTNAME,'stage')
    r.hdel(HOSTNAME,'start')
    r.hdel(HOSTNAME,'pipeline')

