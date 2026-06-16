import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { CatalogueCategory } from '../../core/models/catalogue.models';
import { forkJoin, of } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Component({
  selector: 'app-post',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './post.component.html',
  styleUrls: ['./post.component.css']
})
export class PostComponent implements OnInit {
  private fb = inject(FormBuilder);
  private api = inject(CatalogueApiService);
  private router = inject(Router);

  postForm: FormGroup = this.fb.group({
    titre: ['', Validators.required],
    categorie_id: ['', Validators.required],
    description: ['', Validators.required],
    prix: ['', [Validators.required, Validators.min(0)]],
    coordonnees: [''],
    ville_nom: ['', Validators.required],
    code_postal: ['', Validators.required]
  });

  selectedFiles: File[] = [];
  categories: CatalogueCategory[] = [];
  isLoading = false;
  errorMessage = '';

  ngOnInit() {
    this.api.getCategories().subscribe({
      next: (categories) => {
        this.categories = categories;
      },
      error: (err) => {
        console.error('Erreur lors du chargement des catégories', err);
      }
    });
  }

  onFileChange(event: any) {
    if (event.target.files && event.target.files.length > 0) {
      this.selectedFiles = Array.from(event.target.files);
    }
  }

  onSubmit() {
    if (this.postForm.valid && this.selectedFiles.length > 0) {
      this.isLoading = true;
      this.errorMessage = '';
      
      const uploadObservables = this.selectedFiles.map(file => 
        this.api.uploadImage(file).pipe(
          catchError(err => {
            console.error('Erreur upload image', err);
            return of(null);
          })
        )
      );

      forkJoin(uploadObservables).subscribe(results => {
        const successfulUploads = results.filter(res => res && res.success);
        const imageIds = successfulUploads.map(res => res.id);

        if (imageIds.length === 0) {
          this.isLoading = false;
          this.errorMessage = 'Erreur lors du téléchargement des images.';
          return;
        }

        const formData = new FormData();
        
        // Append form fields
        Object.keys(this.postForm.value).forEach(key => {
          formData.append(key, this.postForm.value[key]);
        });

        // Append image IDs
        imageIds.forEach((id) => {
          formData.append('images[]', id);
        });

        this.api.postItem(formData).subscribe({
          next: (response) => {
            this.isLoading = false;
            console.log('Annonce publiée avec succès', response);
            if (response && response.article_id) {
              this.router.navigate(['/item', response.article_id]);
            } else if (response) {
              this.router.navigate(['/item', response]); // L'API renvoie l'ID directement dans result parfois
            } else {
              this.router.navigate(['/catalogue']);
            }
          },
          error: (error) => {
            this.isLoading = false;
            console.error('Erreur lors de la publication', error);
            this.errorMessage = error.error?.message || error.error?.errors?.[0] || 'Une erreur est survenue lors de la publication.';
          }
        });
      });
    } else {
      this.postForm.markAllAsTouched();
    }
  }
}
