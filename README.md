# 🌐 API REST – 97Car

Ceci est l’API backend du projet **97Car**, développée avec **Slim 4 (PHP)**.  
Elle permet à l’application Slim Android de gérer les **clients**, **voitures** et **factures** via des requêtes HTTP en **JSON**.

Le backend utilise **PostgreSQL** et est entièrement conteneurisé avec **Docker**.

---

## ✅ Fonctionnalités

- Ajouter / modifier / supprimer des **clients**
- Gérer les **voitures** associées à chaque client
- Consulter  des **factures** lier à une voiture ou un client


---

## 🛠️ Stack technique

- PHP 7.4+
- Slim Framework 4
- PostgreSQL
- Adminer
- Docker + Docker Compose

---

## ▶️ Lancer le projet

```bash
git clone https://github.com/lmoutanin/API-97CAR.git
cd API-97CAR
docker-compose up -d
```

Une fois démarré, l’API est accessible sur :  
`http://localhost:8080`

Et la base de donnée est accessible  avec Adminer sur : 
`http://localhost:8181`


---

## 🔗 Lien vers l’application Android

👉 [97CAR_Android – GitHub](https://github.com/lmoutanin/97CAR_Android)
