<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Exports\StudentsExport;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Tables\Actions\Action;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->autofocus()
                    ->required(),
                Forms\Components\TextInput::make('phone_number')
                    ->label('Phone number')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->revealable(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->required(),
                Forms\Components\Select::make('class_id')
                    ->live()
                    ->relationship(name: 'class', titleAttribute: 'name'),
                // ->relationship('class', 'name'),

                Forms\Components\Select::make('section_id')
                    ->label('Section')
                    ->options(function (Get $get) {
                        $classId = $get('class_id');
                        info($classId);
                        if ($classId) {
                            return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                        }
                    })


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('class.name')
                    ->badge()->searchable(),
                TextColumn::make('section.name')
                    ->badge()->searchable(),

            ])
            ->filters([
                Filter::make('class-section-filter')
                    ->form([

                        Select::make('class_id')
                            ->label('Filter by class')
                            ->placeholder('Select a class')
                            ->options(
                                Classes::pluck('name', 'id')->toArray(),
                            ),
                        Select::make('section_id')
                            ->label('Filter by Section')
                            ->placeholder('Select a Section')
                            ->options(
                                function (Get $get) {
                                    $classId = $get('class_id');
                                    if ($classId) {
                                        return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                                    }
                                }
                            ),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        info($data['class_id']);
                        return $query->when($data['class_id'], function ($query) use ($data) {
                            return $query->where('class_id', $data['class_id']);
                        })->when($data['section_id'], function ($query) use ($data) {
                            return $query->where('section_id', $data['section_id']);
                        });
                    })
            ])
            ->actions([
                Action::make('downloadPdf')->url(function (Student $student) {
                    return route('student.invoice.generate', $student);
                }),
                Action::make('qrCode')
                    ->url(function (Student $record) {
                        return static::getUrl('qrCode', ['record' => $record]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export')
                        ->label('Export')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            return Excel::download(new StudentsExport($records), 'students.xlsx');
                        })
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'qrCode' => Pages\GenerateQrCode::route('/{record}/qrcode'),
        ];
    }
}
