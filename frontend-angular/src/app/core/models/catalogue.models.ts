export type CatalogueCategory = {
  id: number;
  nom: string;
};

export type CatalogueItem = {
  id: number;
  vendeur_id: string;
  categorie_id: number;
  titre: string;
  description: string;
  prix: number;
  statut: string;
  ville_nom: string | null;
  code_postal: string | null;
  categorie_nom: string;
  image: string;
  isFavoris: boolean;
};

export type CatalogueFilters = {
  search?: string;
  categorie?: string;
  ville?: string;
  distance?: string;
  prix_min?: string;
  prix_max?: string;
  tri?: string;
};