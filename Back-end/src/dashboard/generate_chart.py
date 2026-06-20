#!/usr/bin/env python3
import os
import sys
import numpy as np

# On force Matplotlib à utiliser le dossier /tmp pour son cache
os.environ['MPLCONFIGDIR'] = '/tmp/matplotlib_cache'

import warnings
warnings.filterwarnings("ignore", category=UserWarning)


import matplotlib.pyplot as plt
import seaborn as sns
from pymongo import MongoClient
import mysql.connector
import pandas as pd

try:
    # ==========================================
    # 1. CONNEXION ET REQUÊTES MYSQL
    # ==========================================
    mysql_conn = mysql.connector.connect(
        host="db",
        user="root",
        password="rootpassword", 
        database="WE4BDB"  # Reprend exactement le nom de ton init.sql
    )

    # A. Ventes par catégorie (Pour le Radar Chart)
    query_sales_cat = """
        SELECT c.nom as categorie, COUNT(v.id) as total_ventes
        FROM categories c
        LEFT JOIN articles a ON a.categorie_id = c.id
        LEFT JOIN ventes v ON v.article_id = a.id
        GROUP BY c.nom
    """
    df_sales_cat = pd.read_sql(query_sales_cat, mysql_conn)

    # B. Évolution mensuelle (Volume de ventes et Chiffre d'affaires)
    query_evo = """
        SELECT DATE_FORMAT(created_at, '%Y-%m') as mois, 
               COUNT(*) as volume_ventes, 
               SUM(montant) as chiffre_affaires
        FROM ventes
        GROUP BY mois
        ORDER BY mois ASC
    """
    df_evo = pd.read_sql(query_evo, mysql_conn)
    mysql_conn.close()

    # ==========================================
    # 2. CONNEXION ET TRAITEMENT MONGODB
    # ==========================================
    mongo_client = MongoClient("mongodb://root:mongopassword@mongodb:27017/")
    db_mongo = mongo_client["WE4BDB"]
    collection = db_mongo["api_history"]
    mongo_data = list(collection.find({}, {"status_code": 1, "timestamp": 1, "action": 1, "payload": 1, "_id": 0}))

    # Initialisation des compteurs de clics par défaut si Mongo est vide
    clics_par_cat = {cat: 0 for cat in df_sales_cat['categorie']}

    if mongo_data:
        df_mongo = pd.DataFrame(mongo_data)
        df_mongo['timestamp'] = pd.to_datetime(df_mongo['timestamp'])
        df_mongo['Jour'] = df_mongo['timestamp'].dt.day_name()
        df_mongo['Heure'] = df_mongo['timestamp'].dt.hour
        
        # Extraction des clics par catégorie depuis les logs de l'action 'item'
        # On regarde si l'admin/user a chargé un item et on vérifie la catégorie dans le payload si disponible
        for log in mongo_data:
            if log.get('action') == 'item' and log.get('payload'):
                # Si ton front envoie la catégorie ou le nom dans le payload
                cat_payload = log['payload'].get('categorie_nom') or log['payload'].get('categorie')
                if cat_payload in clics_par_cat:
                    clics_par_cat[cat_payload] += 1
    
    # Intégration des clics de Mongo dans le DataFrame des catégories
    df_sales_cat['clics'] = df_sales_cat['categorie'].map(clics_par_cat).fillna(0)

    # ==========================================
    # 3. GÉNÉRATION DE LA MOSAÏQUE (4 Graphiques)
    # ==========================================
    fig = plt.figure(figsize=(18, 14))
    
    # --- GRAPHIQUE 1 : Camembert des Statuts API (Top Gauche) ---
    ax1 = fig.add_subplot(2, 2, 1)
    if mongo_data:
        status_counts = df_mongo['status_code'].value_counts()
        colors = ['#2ecc71', '#f39c12', '#e74c3c', '#3498db']
        status_counts.plot(kind='pie', autopct='%1.1f%%', startangle=90, 
                           colors=colors[:len(status_counts)], ax=ax1,
                           wedgeprops={'edgecolor': 'white', 'linewidth': 1})
        ax1.set_title("Statuts des requêtes HTTP (MongoDB)", fontsize=12, weight='bold')
        ax1.set_ylabel("")

    # --- GRAPHIQUE 2 : Heatmap Temporelle (Top Droite) ---
    ax2 = fig.add_subplot(2, 2, 2)
    if mongo_data:
        pivot_table = df_mongo.pivot_table(index='Jour', columns='Heure', values='status_code', aggfunc='count').fillna(0)
        jours_ordonnes = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
        pivot_table = pivot_table.reindex(jours_ordonnes).fillna(0)
        sns.heatmap(pivot_table, cmap="YlGnBu", annot=False, cbar=True, ax=ax2)
        ax2.set_title("Intensité du Trafic (Jours vs Heures - MongoDB)", fontsize=12, weight='bold')
        ax2.set_xlabel("Heure de la journée")
        ax2.set_ylabel("Jour de la semaine")

    # --- GRAPHIQUE 3 : Le Graphique en Étoile / Radar (Bas Gauche) ---
    # Un graphique radar nécessite une projection polaire
    ax3 = fig.add_subplot(2, 2, 3, projection='polar')
    
    categories = list(df_sales_cat['categorie'])
    N = len(categories)
    
    if N > 0:
        angles = [n / float(N) * 2 * np.pi for n in range(N)]
        angles += angles[:1] # Fermer la boucle de l'étoile
        
        # Normalisation des échelles pour pouvoir comparer Clics et Ventes sur le même graphique
        ventes = list(df_sales_cat['total_ventes'])
        ventes += ventes[:1]
        clics = list(df_sales_cat['clics'])
        clics += clics[:1]
        
        # Tracer la zone des Ventes
        ax3.plot(angles, ventes, linewidth=2, linestyle='solid', label="Ventes Réelles (MySQL)", color='#e74c3c')
        ax3.fill(angles, ventes, color='#e74c3c', alpha=0.2)
        
        # Tracer la zone des Clics
        ax3.plot(angles, clics, linewidth=2, linestyle='solid', label="Intérêt / Clics (MongoDB)", color='#3498db')
        ax3.fill(angles, clics, color='#3498db', alpha=0.2)
        
        ax3.set_xticks(angles[:-1])
        ax3.set_xticklabels(categories, fontsize=10)
        ax3.set_title("Performance par Catégorie : Clics vs Ventes", fontsize=12, weight='bold', pad=20)
        ax3.legend(loc='upper right', bbox_to_anchor=(1.3, 1.1))

    # --- GRAPHIQUE 4 : Évolution Linéaire des Ventes (Bas Droite) ---
    ax4 = fig.add_subplot(2, 2, 4)
    if not df_evo.empty:
        # Courbe du Volume de ventes (Axe gauche)
        color = '#2c3e50'
        ax4.set_xlabel('Mois', weight='bold')
        ax4.set_ylabel('Volume de transactions', color=color, weight='bold')
        line1 = ax4.plot(df_evo['mois'], df_evo['volume_ventes'], color=color, marker='o', linewidth=2, label="Nb Ventes")
        ax4.tick_params(axis='y', labelcolor=color)
        ax4.grid(True, linestyle='--', alpha=0.5)
        
        # Double axe pour le Chiffre d'Affaires (Axe Droite)
        ax4_ca = ax4.twinx()  
        color_ca = '#2ecc71'
        ax4_ca.set_ylabel('Chiffre d\'Affaires (€)', color=color_ca, weight='bold')
        line2 = ax4_ca.plot(df_evo['mois'], df_evo['chiffre_affaires'], color=color_ca, marker='s', linewidth=2, linestyle='--', label="CA (€)")
        ax4_ca.tick_params(axis='y', labelcolor=color_ca)
        
        # Jointure des légendes
        lines = line1 + line2
        labels = [l.get_label() for l in lines]
        ax4.legend(lines, labels, loc='upper left')
        ax4.set_title("Croissance Mensuelle (MySQL)", fontsize=12, weight='bold')
        plt.setp(ax4.get_xticklabels(), rotation=30, ha="right")
    else:
        ax4.text(0.5, 0.5, "En attente de vos premières transactions de ventes...", ha='center', va='center', fontsize=11)

    # ==========================================
    # 4. SAUVEGARDE DU DASHBOARD MATRICE
    # ==========================================
    output_path = "/var/www/html/public/img/chart_status.png"
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    
    plt.tight_layout()
    plt.savefig(output_path, dpi=130) # dpi=130 pour garder les textes très nets
    plt.close()
    
    print("Success")

except Exception as e:
    print(f"Error: {str(e)}")
    sys.exit(1)