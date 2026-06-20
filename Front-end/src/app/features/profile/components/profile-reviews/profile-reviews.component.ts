import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-profile-reviews',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './profile-reviews.component.html'
})
export class ProfileReviewsComponent {
  @Input() user: any = null;
  @Input() reviews: any[] = [];
}
