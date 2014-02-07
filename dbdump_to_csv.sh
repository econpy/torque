#!/bin/bash

# Default MySQL database and table names
DBNAME=torque
TABLE=raw_logs

# Location of your MySQL options file
OPTFILE=$HOME/.my.cnf

# Define full path of current directory
CURDIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)

# Make a `torque_data` directory if it doesn't already exist and ensure it can be written to by MySQL
mkdir -p $CURDIR/torque_data
chmod a+rwx $CURDIR/torque_data/
OUTDIR=$CURDIR/torque_data

# Define the name of the output CSV file
FNAME=$OUTDIR/$(date +%Y.%m.%d)-$DBNAME.csv

# Create an empty file and set up column names
mysql --defaults-file=$OPTFILE $DBNAME -B -e "SELECT COLUMN_NAME FROM information_schema.COLUMNS C WHERE table_name = '$TABLE';" | awk '{print $1}' | grep -iv ^COLUMN_NAME$ | sed 's/^/"/g;s/$/"/g' | tr '\n' ',' > $FNAME

# Append newline to mark beginning of data vs. column titles
echo "" >> $FNAME

# Dump data from DB into temp file
mysql --defaults-file=$OPTFILE $DBNAME -B -e "SELECT * INTO OUTFILE '$OUTDIR/tempfile.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM $TABLE;"

# Merge data file and file with column names
cat $OUTDIR/tempfile.csv >> $FNAME

# Delete temp file
rm -rf $OUTDIR/tempfile.csv

echo "File saved to:" $FNAME
