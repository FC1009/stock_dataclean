import requests
import numpy as np
import pandas as pd

link = 'https://quality.data.gov.tw/dq_download_json.php?nid=11549&md5_url=bb878d47ffbe7b83bfc1b41d0b24946e'
r = requests.get(link)
data = pd.DataFrame(r.json())

data = data[["證券代號","證券名稱"]]
data.to_csv('./stock_id.csv', index=False, header = True, encoding = 'utf-8-sig')
