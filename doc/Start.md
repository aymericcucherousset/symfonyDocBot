# Start

Copy [.env](../.env) to [.env.local](../.env.local):  
```cp .env .env.local && cp .env.docker .env.docker.local```  

Setting up OpenAI Api key in [.env.local](../.env.local) file:  
```sed -i 's/sk-your-key-here/REPLACE-WITH-YOUR_KEY/g' .env.local```  

Install project:
```make install```  

Open your web browser on [http://127.0.0.1:8000](http://127.0.0.1:8000)  

Download Symfony doc from repository & generate embedding:  
Note: replace 6.4 with your version  
```make install-documentation 6.4```  
