import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { CatalogueItem } from '../../core/models/catalogue.models';

@Component({
  selector: 'app-item-detail',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './item-detail.component.html'
})
export class ItemDetailComponent {
  item: CatalogueItem | null = null;

  constructor(route: ActivatedRoute, private api: CatalogueApiService) {
    const itemId = Number(route.snapshot.paramMap.get('id'));
    this.api.getItem(itemId).subscribe({
      next: (data) => this.item = data,
      error: () => this.item = null
    });
  }
}