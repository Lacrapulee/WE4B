import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterLink, Router } from '@angular/router';
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
  isOwner: boolean = false;
  isDeleting: boolean = false;
  selectedImageIndex: number = 0;
  readonly backendUrl = 'http://localhost:8000';

  constructor(private route: ActivatedRoute, private router: Router, private api: CatalogueApiService) {}

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
    this.isOwner = false;
    
    this.api.getItem(id).subscribe({
      next: (data) => {
        this.itemData = data;
        this.item = data.item;
        this.images = data.images || ['default.png'];
        this.similarAds = data.similarAds || [];
        this.isOwner = !!data.isOwner;
        
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

  selectImage(index: number) {
    this.selectedImageIndex = index;
  }

  prevImage() {
    if (this.images.length > 0) {
      this.selectedImageIndex = (this.selectedImageIndex > 0) ? this.selectedImageIndex - 1 : this.images.length - 1;
    }
  }

  nextImage() {
    if (this.images.length > 0) {
      this.selectedImageIndex = (this.selectedImageIndex < this.images.length - 1) ? this.selectedImageIndex + 1 : 0;
    }
  }

  deleteItem() {
    if (this.item && confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
      this.isDeleting = true;
      this.api.deleteItem(this.item.id).subscribe({
        next: (response) => {
          if (response.success) {
            this.router.navigate(['/catalogue']);
          } else {
            alert(response.error || 'Erreur lors de la suppression');
            this.isDeleting = false;
          }
        },
        error: (err) => {
          console.error(err);
          alert('Erreur réseau ou permission refusée');
          this.isDeleting = false;
        }
      });
    }
  }
}
