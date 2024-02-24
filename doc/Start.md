# Start

Copy [.env](../.env) to [.env.local](../.env.local):  
```cp .env .env.local```  

Setting up OpenAI Api key in [.env.local](../.env.local) file:  
```nano .env.local```  

Find OPENAI_API_KEY value and set your key:  
```sed -i 's/sk-your-key-here/YOUR_KEY/g' .env.local```  

Install php dependencies:  
```composer install```

Start PostgreSQL database:  
```docker compose up -d```

Install migrations:  
```symfony console d:m:m --no-interaction```

Run the project:  
```symfony serve```  

Open your web browser on [http://127.0.0.1:8000](http://127.0.0.1:8000)  

Download Symfony doc from repository (replace 6.4 with your version):  
```symfony console doc:download 6.4```  

Generate embedding (replace 6.4 with your version):  
```symfony console embedding:generate 6.4```  
