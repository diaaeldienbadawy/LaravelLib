<?php

namespace App\Lib\Http\HttpStructure\Enums;

enum ImagePath: string
{
    case general = 'assets/uploads/images';
    case articals = 'assets/uploads/images/Articals';
    case articalElements = 'assets/uploads/images/Articals/Elements';
    case ProfilePictures = 'assets/uploads/images/ProfilePictures';
    case ProfileCertificates = 'assets/uploads/images/certificates';
    case events = 'assets/uploads/images/events';
    case heroSection = 'assets/uploads/images/heroSection';
    case services = 'assets/uploads/images/services';
    case profileImage = 'assets/uploads/images/profile';
    case profileCerificateImage = 'assets/uploads/images/profile/certificates';
    case reviewImage = 'assets/uploads/images/reviews';
    case beforeAndAfterImage = 'assets/uploads/images/beforeAndAfter';
}
