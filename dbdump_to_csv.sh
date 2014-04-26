#!/bin/bash

# Default MySQL database and table names
DBNAME=torque
TABLE=raw_logs

# Location of your MySQL options file
OPTFILE=$HOME/.my.cnf

# Define full path of current directory
CURDIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)

# Make a `torque_data` directory if it doesn't already exist
mkdir -p $CURDIR/torque_data
OUTDIR=$CURDIR/torque_data

# Create a temporary directory where MySQL should be able to write
TMPDIR=/tmp/torque_data
mkdir -p $TMPDIR
chmod a+rwx $TMPDIR
TMPFILE=$TMPDIR/tempfile.csv

# Define the name of the output CSV file
FNAME=$OUTDIR/$(date +%Y.%m.%d)-$DBNAME.csv

# Create an empty file and set up column names
mysql --defaults-file=$OPTFILE $DBNAME -B -e "SELECT COLUMN_NAME FROM information_schema.COLUMNS C WHERE table_name = '$TABLE';" | awk '{print $1}' | grep -iv ^COLUMN_NAME$ | sed 's/^/"/g;s/$/"/g' | tr '\n' ',' > $FNAME

# Append newline to mark beginning of data vs. column titles
echo "" >> $FNAME

# Dump data from DB into temp file
mysql --defaults-file=$OPTFILE $DBNAME -B -e "SELECT * INTO OUTFILE '$TMPFILE' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM $TABLE;"

# Merge data file and file with column names
cat $TMPFILE >> $FNAME

# Delete temp file
rm -rf $TMPFILE

echo "File saved to:" $FNAME
