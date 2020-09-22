import os
import pandas as pd

df =pd.read_csv('./stock_id_select.csv')
l1=df['證券代號'].tolist()

for i in l1:
    cmd_str='/opt/app/get-yahoo-quotes/get-yahoo-quotes.sh {}.TW'.format(i)
    os.system(cmd_str)