import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';

@Component({
  selector: 'app-item-detail',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './item-detail.component.html'
})
export class ItemDetailComponent implements OnInit {
  itemData: any = null;
  item: any = null;
  images: string[] = [];
  similarAds: any[] = [];
  vendeur: any = null;
  loading: boolean = true;

  constructor(private route: ActivatedRoute, private api: CatalogueApiService) {}

  ngOnInit() {
    this.route.paramMap.subscribe(params => {
      const itemId = Number(params.get('id'));
      if (itemId) {
        this.loadItem(itemId);
      }
    });
  }

  loadItem(id: number) {
    this.loading = true;
    this.item = null;
    this.vendeur = null;
    
    this.api.getItem(id).subscribe({
      next: (data) => {
        this.itemData = data;
        this.item = data.item;
        this.images = data.images || ['default.png'];
        this.similarAds = data.similarAds || [];
        
        // Load seller info if we have a vendeur_id
        if (this.item && this.item.vendeur_id) {
          // Fallback en attendant la requête, ou si elle échoue
          if (this.item.vendeur_prenom || this.item.vendeur_nom) {
             this.vendeur = {
               nom: this.item.vendeur_nom || '',
               prenom: this.item.vendeur_prenom || ''
             };
          }

          this.api.getUser(this.item.vendeur_id).subscribe({
            next: (userData) => {
              if (userData && userData.user) {
                this.vendeur = userData.user;
              }
            },
            error: (err) => console.error("Erreur chargement vendeur", err)
          });
        }
        
        this.loading = false;
      },
      error: () => {
        this.item = null;
        this.loading = false;
      }
    });
  }
}
