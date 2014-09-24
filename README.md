insightAnalyse
==============

This script is lunched on CLI, to show statistiques from Insight of your Commits.

==============
To run this script you need: insight.phar ( from 

# 1.

$ curl -o insight.phar -s http://get.insight.sensiolabs.com/insight.phar
# or
$ wget http://get.insight.sensiolabs.com/insight.phar

# 2. 

Change the project Uuid in loadInsightXMl() methode. ex: 0fc78-a2ed-410e-9a5b-f8fef3264


NB: this is a shell script that gives git Logs in Json format: git-log2json.sh
