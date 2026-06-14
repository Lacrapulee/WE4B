import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { CatalogueCategory } from '../../core/models/catalogue.models';

@Component({
  selector: 'app-edit-item',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './edit-item.component.html'
})
export class EditItemComponent implements OnInit {
  private fb = inject(FormBuilder);
  private api = inject(CatalogueApiService);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  editForm: FormGroup = this.fb.group({
    titre: ['', Validators.required],
    categorie_id: ['', Validators.required],
    description: ['', Validators.required],
    prix: ['', [Validators.required, Validators.min(0)]],
    statut: ['en_ligne', Validators.required]
  });

  itemId: number | null = null;
  categories: CatalogueCategory[] = [];
  isLoading = false;
  isSaving = false;
  errorMessage = '';

  ngOnInit() {
    this.route.paramMap.subscribe(params => {
      this.itemId = Number(params.get('id'));
      if (this.itemId) {
        this.loadData();
      }
    });
  }

  loadData() {
    this.isLoading = true;
    
    // Charger les catégories d'abord
    this.api.getCategories().subscribe({
      next: (categories) => {
        this.categories = categories;
        
        // Puis charger l'annonce
        if (this.itemId) {
          this.api.getItem(this.itemId).subscribe({
            next: (data) => {
              const item = data.item;
              if (item) {
                this.editForm.patchValue({
                  titre: item.titre,
                  categorie_id: item.categorie || item.categorie_id,
                  description: item.description,
                  prix: item.prix,
                  statut: item.statut || 'en_ligne'
                });
              }
              this.isLoading = false;
            },
            error: (err) => {
              console.error('Erreur de chargement', err);
              this.errorMessage = 'Impossible de charger les informations de l\'annonce.';
              this.isLoading = false;
            }
          });
        }
      },
      error: (err) => {
        console.error('Erreur lors du chargement des catégories', err);
        this.isLoading = false;
      }
    });
  }

  onSubmit() {
    if (this.editForm.valid && this.itemId) {
      this.isSaving = true;
      this.errorMessage = '';
      
      this.api.editItem(this.itemId, this.editForm.value).subscribe({
        next: (response) => {
          this.isSaving = false;
          if (response.success) {
            this.router.navigate(['/item', this.itemId]);
          } else {
            this.errorMessage = response.message || 'Erreur lors de la modification.';
          }
        },
        error: (err) => {
          this.isSaving = false;
          this.errorMessage = err.error?.error || err.error?.message || 'Erreur réseau.';
          console.error(err);
        }
      });
    } else {
      this.editForm.markAllAsTouched();
    }
  }
}
